<?php
namespace codename\core\bucket;
use \codename\core\app;
use codename\core\exception;
use codename\login\context\remote;

/**
 * I can manage files on a FTP server.
 * @package core
 * @since 2016-05-18
 */
class ftp extends \codename\core\bucket implements \codename\core\bucket\bucketInterface {

    /**
     * Contains the FTP connection stream
     * @var resource
     */
    protected $connection = null;

    /**
     * Contains the public base URL to the webspace where files are located
     * @var string
     */
    public $baseurl = '';

    /**
     * Creates the instance, establishes the connection and authenticates
     * @param array $data
     * @return \codename\core\bucket
     */
    public function __construct(array $data) {
        parent::__construct($data);

        if(count($errors = app::getValidator('structure_config_bucket_ftp')->validate($data)) > 0) {
            $this->errorstack->addError('CONFIGURATION', 'CONFIGURATION_INVALID', $errors);
            return $this;
        }

        $this->basedir = $data['basedir'];

        if($data['ftpserver']['ssl'] ?? false) {
          $this->connection = @ftp_ssl_connect($data['ftpserver']['host'], $data['ftpserver']['port'], 2);
        } else {
          $this->connection = @ftp_connect($data['ftpserver']['host'], $data['ftpserver']['port'], 2);
        }

        if(isset($data['public']) && $data['public']) {
            $this->baseurl = $data['baseurl'];
        }

        if(is_bool($this->connection) && !$this->connection) {
            $this->errorstack->addError('FILE', 'CONNECTION_FAILED', null);
            app::getLog('errormessage')->warning('CORE_BACKEND_CLASS_BUCKET_FTP_CONSTRUCT::CONNECTION_FAILED ($host = ' . $data['ftpserver']['host'] .')');
            throw new exception('EXCEPTION_BUCKET_FTP_CONNECTION_FAILED', exception::$ERRORLEVEL_ERROR, [ 'host' => $data['ftpserver']['host'] ]);
            return $this;
        }

        if(!@ftp_login($this->connection, $data['ftpserver']['user'], $data['ftpserver']['pass'])) {
            $this->errorstack->addError('FILE', 'LOGIN_FAILED', null);
            app::getLog('errormessage')->warning('CORE_BACKEND_CLASS_BUCKET_FTP_CONSTRUCT::LOGIN_FAILED ($user = ' . $data['ftpserver']['user'] .')');
            throw new exception('EXCEPTION_BUCKET_FTP_LOGIN_FAILED', exception::$ERRORLEVEL_ERROR, [ 'user' => $data['ftpserver']['user'] ]);
            return $this;
        }

        // passive mode setting from config
        if($data['ftpserver']['passive_mode'] ?? false) {
          $this->enablePassiveMode(true);
        }

        return $this;
    }

    /**
     * [enablePassiveMode description]
     * @param  bool   $state [description]
     * @return [type]        [description]
     */
    public function enablePassiveMode(bool $state) {
      @ftp_pasv($this->connection, $state);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::filePush($localfile, $remotefile)
     */
    public function filePush(string $localfile, string $remotefile) : bool {
        if(!app::getFilesystem()->fileAvailable($localfile)) {
            $this->errorstack->addError('FILE', 'LOCAL_FILE_NOT_FOUND', $localfile);
        }

        if($this->fileAvailable($remotefile)) {
            $this->fileDelete($remotefile);
        }

        $directory = $this->extractDirectory($remotefile);

        if(!$this->dirAvailable($directory)) {
            $this->dirCreate($directory);
        }

        try {
          @ftp_put($this->connection, $this->basedir . $remotefile, $localfile, FTP_BINARY);
        } catch (\Exception $e) {
          $this->errorstack->addError('FILE', 'FILE_PUSH_FAILED', $this->basedir . $remotefile);
        }

        return $this->fileAvailable($remotefile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::filePull($remotefile, $localfile)
     */
    public function filePull(string $remotefile, string $localfile) : bool {
        if(app::getFilesystem()->fileAvailable($localfile)) {
            $this->errorstack->addError('FILE', 'LOCAL_FILE_EXISTS', $localfile);
        }

        if(!$this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_NOT_FOUND', $remotefile);
        }

        @ftp_get($this->connection, $localfile, $this->basedir . $remotefile, FTP_BINARY);

        return app::getFilesystem()->fileAvailable($localfile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::dirAvailable($directory)
     */
    public function dirAvailable(string $directory) : bool {
        $list = $this->getDirlist($directory);
        if(is_bool($list) && !$list) {
            return false;
        }
        return true;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::dirList($directory)
     */
    public function dirList(string $directory) : array {
        if(!$this->dirAvailable($directory)) {
            $this->errorstack->addError('DIRECTORY', 'REMOTE_DIRECTORY_NOT_FOUND', $directory);
            return array();
        }
        $list = $this->getDirlist($directory);
        $myList = array();

        if(!is_array($list)) {
            return $myList;
        }

        foreach($list as $element) {
            $myList[] = str_replace('/', '', str_replace(str_replace('//', '/', $this->basedir . $directory), '', $element));

        }
        return $myList;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::fileAvailable($remotefile)
     */
    public function fileAvailable(string $remotefile) : bool {
        $filenamedata = explode('/', $remotefile);
        $filename = $filenamedata[count($filenamedata) - 1];
        unset($filenamedata[count($filenamedata) - 1]);
        $directory = implode('/', $filenamedata);
        $dirlist = $this->dirList($directory);
        if(in_array($filename, $dirlist)) {
            return true;
        }
        return false;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::fileDelete($remotefile)
     */
    public function fileDelete(string $remotefile) : bool {
        if(!$this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_NOT_FOUND', $remotefile);
            return true;
        }

        @ftp_delete($this->connection, $this->basedir . $remotefile);

        return !$this->fileAvailable($remotefile);
    }

    /**
     * @inheritDoc
     * @see \codename\core\bucket_interface::fileMove($remotefile, $newremotefile)
     */
    public function fileMove(string $remotefile, string $newremotefile): bool
    {
      if(!$this->fileAvailable($remotefile)) {
          $this->errorstack->addError('FILE', 'REMOTE_FILE_NOT_FOUND', $remotefile);
          return true;
      }

      // check for existance of the new file
      if($this->fileAvailable($newremotefile)) {
          $this->errorstack->addError('FILE', 'FILE_ALREADY_EXISTS', $newremotefile);
          return false;
      }

      @ftp_rename($this->connection, $this->basedir . $remotefile, $this->basedir . $newremotefile);

      return $this->fileAvailable($newremotefile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket\bucketInterface::isFile()
     */
    public function isFile(string $remotefile) : bool {
        $list = $this->getRawlist($this->extractDirectory($remotefile));
        if(!is_array($list)) {
            return false;
        }
        foreach($list as $file) {
            if(strpos($file, $this->extractFilename($remotefile)) !== false) {
                return substr($file, 0,1) == 'd' ? false : true;
            }
        }
        return false;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::fileGetUrl($remotefile)
     */
    public function fileGetUrl(string $remotefile) : string {
        return $this->baseurl . $remotefile;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::fileGetInfo($remotefile)
     */
    public function fileGetInfo(string $remotefile) : array {}

    /**
     * Creates the given $directory on this instance's remote hostname
     * @param string $dirname
     * @return boolean
     */
    public function dirCreate(string $directory) {
        if($this->dirAvailable($directory)) {
            return true;
        }

        @ftp_mkdir($this->connection, $directory);

        return $this->dirAvailable($directory);
    }

    /**
     * Nested function to retrieve a directory List
     * @param string $directory
     * @return array | null
     */
    protected function getDirlist(string $directory) {
        return @ftp_nlist($this->connection, $this->basedir . $directory);
    }

    /**
     * Nested functino to retrieve a RAW directory list
     * @param string $directory
     * @return array | null
     */
    protected function getRawlist(string $directory) {
        return @ftp_rawlist($this->connection, $this->basedir . $directory);
    }

    /**
     * Extracts the directory path from $filename
     * <br /><b>example:</b>
     * <br />$name = extractDirectory('/path/to/file.mp3');
     * <br />
     * <br />// $name is now '/path/to/'
     * @param string $filename
     * @return string
     */
    protected function extractDirectory(string $filename) : string {
        $filenamedata = explode('/', $filename);
        unset($filenamedata[count($filenamedata) - 1]);
        return implode('/', $filenamedata);
    }

    /**
     * Extracts the file name from $filename
     * <br /><b>example:</b>
     * <br />$name = extractDirectory('/path/to/file.mp3');
     * <br />
     * <br />// $name is now 'file.mp3'
     * @param string $filename
     * @return string
     */
    protected function extractFilename(string $filename) : string {
        $filenamedata = explode('/', $filename);
        return $filenamedata[count($filenamedata) - 1];
    }

}
