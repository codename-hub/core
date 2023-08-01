<?php

namespace codename\core\bucket;

use codename\core\app;
use codename\core\bucket;
use codename\core\exception;
use FTP\Connection;
use ReflectionException;

/**
 * I can manage files on an FTP server.
 * @package core
 * @since 2016-05-18
 */
class ftp extends bucket implements bucketInterface
{
    /**
     * Contains the public base URL to the webspace where files are located
     * @var string
     */
    public string $baseurl = '';
    /**
     * Contains the FTP connection stream
     * @var Connection|bool|null
     */
    protected Connection|bool|null $connection = null;
    /**
     * Timeout for the FTP connection or followup operations
     * Defaults to 2 seconds
     * @var int|null
     */
    protected ?int $timeout = null;

    /**
     * Creates the instance, establishes the connection and authenticates
     * @param array $data
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        if (count($errors = app::getValidator('structure_config_bucket_ftp')->reset()->validate($data)) > 0) {
            $this->errorstack->addError('CONFIGURATION', 'CONFIGURATION_INVALID', $errors);
            throw new exception(self::EXCEPTION_CONSTRUCT_CONFIGURATIONINVALID, exception::$ERRORLEVEL_ERROR, $errors);
        }

        $this->basedir = $data['basedir'];

        if (isset($data['public']) && $data['public']) {
            $this->baseurl = $data['baseurl'];
        }

        // Default timeout fallback for FTP network operations
        $this->timeout = $data['timeout'] ?? 2;

        if ($data['ftpserver']['ssl'] ?? false) {
            $this->connection = @ftp_ssl_connect($data['ftpserver']['host'], $data['ftpserver']['port'], $this->timeout);
        } else {
            $this->connection = @ftp_connect($data['ftpserver']['host'], $data['ftpserver']['port'], $this->timeout);
        }

        if (!$this->connection) {
            $this->errorstack->addError('FILE', 'CONNECTION_FAILED');
            app::getLog('errormessage')->warning('CORE_BACKEND_CLASS_BUCKET_FTP_CONSTRUCT::CONNECTION_FAILED ($host = ' . $data['ftpserver']['host'] . ')');
            throw new exception('EXCEPTION_BUCKET_FTP_CONNECTION_FAILED', exception::$ERRORLEVEL_ERROR, ['host' => $data['ftpserver']['host']]);
        }

        if (!@ftp_login($this->connection, $data['ftpserver']['user'], $data['ftpserver']['pass'])) {
            $this->errorstack->addError('FILE', 'LOGIN_FAILED');
            app::getLog('errormessage')->warning('CORE_BACKEND_CLASS_BUCKET_FTP_CONSTRUCT::LOGIN_FAILED ($user = ' . $data['ftpserver']['user'] . ')');
            throw new exception('EXCEPTION_BUCKET_FTP_LOGIN_FAILED', exception::$ERRORLEVEL_ERROR, ['user' => $data['ftpserver']['user']]);
        }

        //
        // Sometimes, the server reports his own IP address
        // which might be wrong or a local address
        // advise the client to ignore it and instead connect
        // to the known endpoint directly
        //
        if ($data['ftpserver']['ignore_passive_address'] ?? false) {
            @ftp_set_option($this->connection, FTP_USEPASVADDRESS, false);
        }

        // passive mode setting from config
        if ($data['ftpserver']['passive_mode'] ?? false) {
            $this->enablePassiveMode(true);
        }

        return $this;
    }

    /**
     * [enablePassiveMode description]
     * @param bool $state [description]
     * @return void [type]        [description]
     */
    public function enablePassiveMode(bool $state): void
    {
        @ftp_pasv($this->connection, $state);
    }

    /**
     *
     * {@inheritDoc}
     * @param string $localfile
     * @param string $remotefile
     * @return bool
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\bucket_interface::filePush($localfile, $remotefile)
     */
    public function filePush(string $localfile, string $remotefile): bool
    {
        // Path sanitization
        $remotefile = $this->normalizeRelativePath($remotefile);

        if (!app::getFilesystem()->fileAvailable($localfile)) {
            $this->errorstack->addError('FILE', 'LOCAL_FILE_NOT_FOUND', $localfile);
            return false;
        }

        if ($this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_EXISTS', $remotefile);
            return false;
        }

        $directory = $this->extractDirectory($remotefile);

        if ($directory != '' && !$this->dirAvailable($directory)) {
            $this->dirCreate($directory);
        }

        try {
            if (!@ftp_put($this->connection, $this->basedir . $remotefile, $localfile)) {
                $this->errorstack->addError('FILE', 'FTP_PUT_ERROR', $this->basedir . $remotefile);
                return false;
            }
        } catch (\Exception) {
            $this->errorstack->addError('FILE', 'FILE_PUSH_FAILED', $this->basedir . $remotefile);
        }

        return $this->fileAvailable($remotefile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::fileAvailable($remotefile)
     */
    public function fileAvailable(string $remotefile): bool
    {
        //
        // Neat trick to check for existence - simply use ftp_size
        // which returns -1 for a nonexisting file
        // NOTE: directories will also return -1
        //

        return @ftp_size($this->connection, $this->basedir . $remotefile) !== -1;
    }

    /**
     * Extracts the directory path from $filename
     * <b>example:</b>
     * $name = extractDirectory('/path/to/file.mp3');
     *
     * // $name is now '/path/to/'
     * @param string $filename
     * @return string
     */
    protected function extractDirectory(string $filename): string
    {
        $directory = pathinfo($filename, PATHINFO_DIRNAME);
        if ($directory == '.') {
            return '';
        } else {
            return $directory;
        }
    }

    /**
     *
     * {@inheritDoc}
     * @param string $directory
     * @return bool
     * @throws exception
     * @see \codename\core\bucket_interface::dirAvailable($directory)
     */
    public function dirAvailable(string $directory): bool
    {
        // Path sanitization
        $directory = $this->normalizeRelativePath($directory);

        return static::ftp_isdir($this->connection, $directory);
    }

    /**
     * [ftp_isdir description]
     * @param $conn_id
     * @param $dir
     * @return bool [type]          [description]
     */
    protected static function ftp_isdir($conn_id, $dir): bool
    {
        // Try to change the directory
        // and automatically go up, if it worked
        if (@ftp_chdir($conn_id, $dir)) {
            ftp_cdup($conn_id);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Creates the given $directory on this instance's remote hostname
     * @param string $directory
     * @return bool
     * @throws exception
     */
    public function dirCreate(string $directory): bool
    {
        // Path sanitization
        $directory = $this->normalizeRelativePath($directory);

        if ($this->dirAvailable($directory)) {
            return true;
        }

        //
        // ftp_mkdir is not recursive
        // therefore, we have to traverse each directory/component
        // of the given path
        //

        // Store current directory for later restoration
        $prevDir = @ftp_pwd($this->connection);

        $parts = explode('/', $directory);
        foreach ($parts as $part) {
            if (!@ftp_chdir($this->connection, $part)) {
                @ftp_mkdir($this->connection, $part);
                @ftp_chdir($this->connection, $part);
            }
        }

        // revert to starting directory.
        @ftp_chdir($this->connection, $prevDir);

        return $this->dirAvailable($directory);
    }

    /**
     *
     * {@inheritDoc}
     * @param string $remotefile
     * @param string $localfile
     * @return bool
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\bucket_interface::filePull($remotefile, $localfile)
     */
    public function filePull(string $remotefile, string $localfile): bool
    {
        // Path sanitization
        $remotefile = $this->normalizeRelativePath($remotefile);

        if (app::getFilesystem()->fileAvailable($localfile)) {
            $this->errorstack->addError('FILE', 'LOCAL_FILE_EXISTS', $localfile);
            return false;
        }

        if (!$this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_NOT_FOUND', $remotefile);
            return false;
        }

        // This might fail due to various reasons
        // read error on remote - or write error on local target path
        if (!@ftp_get($this->connection, $localfile, $this->basedir . $remotefile)) {
            $this->errorstack->addError('FILE', 'FTP_GET_ERROR', [$localfile, $remotefile]);
            return false;
        }

        return app::getFilesystem()->fileAvailable($localfile);
    }

    /**
     *
     * {@inheritDoc}
     * @param string $directory
     * @return array
     * @throws exception
     * @see \codename\core\bucket_interface::dirList($directory)
     */
    public function dirList(string $directory): array
    {
        // Path sanitization
        $directory = $this->normalizeRelativePath($directory);

        if (!$this->dirAvailable($directory)) {
            $this->errorstack->addError('DIRECTORY', 'REMOTE_DIRECTORY_NOT_FOUND', $directory);
            return [];
        }
        $list = $this->getDirlist($directory);
        $myList = [];

        if (!is_array($list)) {
            return $myList;
        }

        $prefix = $directory != '' ? $directory . '/' : '';
        foreach ($list as $element) {
            $myList[] = $prefix . $element;
        }
        return $myList;
    }

    /**
     * Nested function to retrieve a directory List
     * @param string $directory
     * @return array | null
     */
    protected function getDirlist(string $directory): ?array
    {
        return @ftp_nlist($this->connection, $this->basedir . $directory);
    }

    /**
     *
     * {@inheritDoc}
     * @param string $remotefile
     * @return bool
     * @throws exception
     * @see \codename\core\bucket_interface::fileDelete($remotefile)
     */
    public function fileDelete(string $remotefile): bool
    {
        // Path sanitization
        $remotefile = $this->normalizeRelativePath($remotefile);

        if (!$this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_NOT_FOUND', $remotefile);
            return true;
        }

        if (!@ftp_delete($this->connection, $this->basedir . $remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_DELETE_FAILED', $remotefile);
            return false;
        }

        return !$this->fileAvailable($remotefile);
    }

    /**
     * {@inheritDoc}
     * @param string $remotefile
     * @param string $newremotefile
     * @return bool
     * @throws exception
     * @see \codename\core\bucket_interface::fileMove($remotefile, $newremotefile)
     */
    public function fileMove(string $remotefile, string $newremotefile): bool
    {
        // Path sanitization
        $remotefile = $this->normalizeRelativePath($remotefile);
        $newremotefile = $this->normalizeRelativePath($newremotefile);

        if (!$this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_NOT_FOUND', $remotefile);
            return false;
        }

        // check for existence of the new file
        if ($this->fileAvailable($newremotefile)) {
            $this->errorstack->addError('FILE', 'FILE_ALREADY_EXISTS', $newremotefile);
            return false;
        }

        $directory = $this->extractDirectory($newremotefile);
        if ($directory !== '' && !$this->dirAvailable($directory)) {
            $this->dirCreate($directory);
        }
        @ftp_rename($this->connection, $this->basedir . $remotefile, $this->basedir . $newremotefile);

        return $this->fileAvailable($newremotefile);
    }

    /**
     *
     * {@inheritDoc}
     * @param string $remotefile
     * @return bool
     * @throws exception
     * @see bucketInterface::isFile
     */
    public function isFile(string $remotefile): bool
    {
        $remotefile = $this->normalizeRelativePath($remotefile); // Path sanitization
        $list = $this->getRawlist($this->extractDirectory($remotefile));
        if (!is_array($list)) {
            return false;
        }
        foreach ($list as $file) {
            if (str_contains($file, $this->extractFilename($remotefile))) {
                return !(str_starts_with($file, 'd'));
            }
        }
        return false;
    }

    /**
     * Nested function to retrieve a RAW directory list
     * @param string $directory
     * @return array | null
     */
    protected function getRawlist(string $directory): ?array
    {
        return @ftp_rawlist($this->connection, $this->basedir . $directory);
    }

    /**
     * Extracts the file name from $filename
     * <b>example:</b>
     * $name = extractDirectory('/path/to/file.mp3');
     *
     * // $name is now 'file.mp3'
     * @param string $filename
     * @return string
     */
    protected function extractFilename(string $filename): string
    {
        $filenamedata = explode('/', $filename);
        return $filenamedata[count($filenamedata) - 1];
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::fileGetUrl($remotefile)
     */
    public function fileGetUrl(string $remotefile): string
    {
        return $this->baseurl . $remotefile;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::fileGetInfo($remotefile)
     */
    public function fileGetInfo(string $remotefile): array
    {
        return [];
    }
}
