<?php
namespace codename\core\bucket;

use \codename\core\app;

/**
 * I can manage files in a local filesystem.
 * <br />At least I am just a compatable helper to \codename\core\filesystem\local
 * @package core
 * @since 2016-04-21
 */
class local extends \codename\core\bucket implements \codename\core\bucket\bucketInterface {

    /**
     * The given config cannot be validated agains structure_config_bucket_local.
     * <br />See the validator for more info
     * @var string
     */
    CONST EXCEPTION_CONSTRUCT_CONFIGURATIONINVALID = 'EXCEPTION_CONSTRUCT_CONFIGURATIONINVALID';

    /**
     * is TRUE if the bucket's basedir is publically available via HTTP(s)
     * @var bool
     */
    protected $public = false;

    /**
     * If the bucket is $public, this contains the URL the bucket can be accessed via HTTP(s)
     * @var string $baseurl
     */
    public $baseurl = '';

    /**
     *
     * @param array $data
     */
    public function __construct(array $data) {
        $this->errorstack = new \codename\core\errorstack('BUCKET');

        if(count($errors = app::getValidator('structure_config_bucket_local')->validate($data)) > 0) {
            $this->errorstack->addError('CONFIGURATION', 'CONFIGURATION_INVALID', $errors);
            throw new \codename\core\exception(self::EXCEPTION_CONSTRUCT_CONFIGURATIONINVALID, 4, $errors);
        }

        $this->basedir = $data['basedir'];
        $this->public  = $data['public'];

        if($this->public) {
            $this->baseurl = $data['baseurl'];
        }

        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::filePush($localfile, $remotefile)
     */
    public function filePush(string $localfile, string $remotefile) : bool {
        $remotefile = $this->normalizePath($remotefile);
        if(!app::getFilesystem()->fileAvailable($localfile)) {
            $this->errorstack->addError('FILE', 'LOCAL_FILE_NOT_FOUND', $localfile);
            return false;
        }

        if($this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_EXISTS', $remotefile);
            return false;
        }

        app::getLog('debug')->debug('Pushing file ' . $localfile . ' to ' . $remotefile);

        if(!app::getFilesystem()->fileCopy($localfile, $remotefile)) {
          // If Copy not successful, check access rights:
          if(!is_writable($remotefile)) {
            // Access rights/permissions error. directory/file not writable
            throw new \codename\core\exception(self::EXCEPTION_FILEPUSH_FILENOTWRITABLE,\codename\core\exception::$ERRORLEVEL_ERROR, $remotefile);
          } else {
            // Unknown Error
            throw new \codename\core\exception(self::EXCEPTION_FILEPUSH_FILEWRITABLE_UNKNOWN_ERROR,\codename\core\exception::$ERRORLEVEL_FATAL, $remotefile);
          }
        }

        return $this->fileAvailable($remotefile);
    }

    /**
     * File is not writable (permissions)
     * @var string
     */
    const EXCEPTION_FILEPUSH_FILENOTWRITABLE = 'EXCEPTION_FILEPUSH_FILENOTWRITABLE';

    /**
     * File is writable, but unkown other issue
     * @var string
     */
    const EXCEPTION_FILEPUSH_FILEWRITABLE_UNKNOWN_ERROR = 'EXCEPTION_FILEPUSH_FILEWRITABLE_UNKNOWN_ERROR';

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::fileAvailable($remotefile)
     */
    public function fileAvailable (string $remotefile) : bool {
        $remotefile = $this->normalizePath($remotefile);
        app::getLog('debug')->debug('Searching file ' . $remotefile);
        return app::getFilesystem()->fileAvailable($remotefile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::filePull($remotefile, $localfile)
     */
    public function filePull(string $remotefile, string $localfile) : bool {
        $remotefile = $this->normalizePath($remotefile);
        if(!$this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_NOT_FOUND', $remotefile);
            return false;
        }

        if(app::getFilesystem()->fileAvailable($localfile)) {
            $this->errorstack->addError('FILE', 'LOCAL_FILE_EXISTS', $localfile);
            return false;
        }
        app::getFilesystem()->fileCopy($remotefile, $localfile);

        return app::getFilesystem()->fileAvailable($localfile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::fileDelete($remotefile)
     */
    public function fileDelete(string $remotefile) : bool {
        $remotefile = $this->normalizePath($remotefile);
        return app::getFilesystem()->fileDelete($remotefile);
    }

    /**
     * @inheritDoc
     * @see \codename\core\bucket_interface::fileMove($remotefile, $newremotefile)
     */
    public function fileMove(string $remotefile, string $newremotefile): bool
    {
      $remotefile = $this->normalizePath($remotefile);
      $newremotefile = $this->normalizePath($newremotefile);
      return app::getFilesystem()->fileMove($remotefile, $newremotefile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::fileGetUrl($remotefile)
     */
    public function fileGetUrl(string $remotefile) : string {
        if(!$this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_NOT_FOUND', $remotefile);
            return '';
        }

        if(!$this->public) {
            $this->errorstack->addError('FILE', 'BUCKET_NOT_PUBLIC');
            return '';
        }

        return $this->baseurl . $remotefile;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::fileGetInfo($remotefile)
     */
    public function fileGetInfo(string $remotefile) : array {
        $remotefile = $this->normalizePath($remotefile);
        return app::getFilesystem()->getInfo($remotefile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::dirAvailable($directory)
     */
    public function dirAvailable(string $directory) : bool {
        $directory = $this->normalizePath($directory);
        return app::getFilesystem()->dirAvailable($directory);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::dirList($directory)
     */
    public function dirList(string $directory) : array {
        $normalizedDirectory = $this->normalizePath($directory);
        if(!$this->dirAvailable($normalizedDirectory)) {
            $this->errorstack->addError('DIRECTORY', 'REMOTE_DIRECTORY_NOT_FOUND', $directory);
            return array();
        }

        //
        // HACK:
        // change bucket_local::dirList() behaviour to be relative to $directory
        // simply prepend $directory to each entry
        //
        $list = app::getFilesystem()->dirList($normalizedDirectory);
        foreach($list as &$entry) {
          $entry = $directory.$entry;
        }
        return $list;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket\bucketInterface::isFile()
     */
    public function isFile(string $remotefile) : bool {
        return app::getFilesystem()->isFile($this->normalizePath($remotefile));
    }

}
