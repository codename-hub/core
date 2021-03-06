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

        if(count($errors = app::getValidator('structure_config_bucket_local')->reset()->validate($data)) > 0) {
            $this->errorstack->addError('CONFIGURATION', 'CONFIGURATION_INVALID', $errors);
            throw new \codename\core\exception(self::EXCEPTION_CONSTRUCT_CONFIGURATIONINVALID, \codename\core\exception::$ERRORLEVEL_ERROR, $errors);
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

        // Path sanitization
        $remotefile = $this->normalizeRelativePath($remotefile);

        // filename just for usage with FS functions
        $normalizedRemotefile = $this->normalizePath($remotefile);

        if(!app::getFilesystem()->fileAvailable($localfile)) {
            $this->errorstack->addError('FILE', 'LOCAL_FILE_NOT_FOUND', $localfile);
            return false;
        }

        if($this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_EXISTS', $remotefile);
            return false;
        }

        if(!app::getFilesystem()->fileCopy($localfile, $normalizedRemotefile)) {
          // If Copy not successful, check access rights:
          if(!is_writable($normalizedRemotefile)) {
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
        // Path sanitization
        $remotefile = $this->normalizeRelativePath($remotefile);
        $normalizedRemotefile = $this->normalizePath($remotefile);
        return app::getFilesystem()->fileAvailable($normalizedRemotefile) && app::getFilesystem()->isFile($normalizedRemotefile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::filePull($remotefile, $localfile)
     */
    public function filePull(string $remotefile, string $localfile) : bool {
        // Path sanitization
        $remotefile = $this->normalizeRelativePath($remotefile);

        if(!$this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_NOT_FOUND', $remotefile);
            return false;
        }

        if(app::getFilesystem()->fileAvailable($localfile)) {
            $this->errorstack->addError('FILE', 'LOCAL_FILE_EXISTS', $localfile);
            return false;
        }

        $normalizedRemotefile = $this->normalizePath($remotefile);
        if(!app::getFilesystem()->fileCopy($normalizedRemotefile, $localfile)) {
          return false;
        }

        return app::getFilesystem()->fileAvailable($localfile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::fileDelete($remotefile)
     */
    public function fileDelete(string $remotefile) : bool {
        // Path sanitization
        $remotefile = $this->normalizeRelativePath($remotefile);

        if(!$this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_NOT_FOUND', $remotefile);
            return true;
        }

        $normalizedRemotefile = $this->normalizePath($remotefile);
        return app::getFilesystem()->fileDelete($normalizedRemotefile);
    }

    /**
     * @inheritDoc
     * @see \codename\core\bucket_interface::fileMove($remotefile, $newremotefile)
     */
    public function fileMove(string $remotefile, string $newremotefile): bool
    {
      // Path sanitization
      $remotefile = $this->normalizeRelativePath($remotefile);
      $newremotefile = $this->normalizeRelativePath($newremotefile);

      $normalizedRemotefile = $this->normalizePath($remotefile);
      $normalizedNewremotefile = $this->normalizePath($newremotefile);
      return app::getFilesystem()->fileMove($normalizedRemotefile, $normalizedNewremotefile);
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
        // Path sanitization
        $remotefile = $this->normalizeRelativePath($remotefile);
        $normalizedRemotefile = $this->normalizePath($remotefile);
        return app::getFilesystem()->getInfo($normalizedRemotefile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::dirAvailable($directory)
     */
    public function dirAvailable(string $directory) : bool {
        // Path sanitization
        $directory = $this->normalizeRelativePath($directory);
        $normalizedDirectory = $this->normalizePath($directory);
        return app::getFilesystem()->dirAvailable($normalizedDirectory);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::dirList($directory)
     */
    public function dirList(string $directory) : array {
        // Path sanitization
        $directory = $this->normalizeRelativePath($directory);

        if(!$this->dirAvailable($directory)) {
            $this->errorstack->addError('DIRECTORY', 'REMOTE_DIRECTORY_NOT_FOUND', $directory);
            return array();
        }

        $normalizedDirectory = $this->normalizePath($directory);

        //
        // HACK:
        // change bucket_local::dirList() behaviour to be relative to $directory
        // simply prepend $directory to each entry
        //
        $list = app::getFilesystem()->dirList($normalizedDirectory);

        // At this point, we use $directory from above as "helper"
        // but internally rely on data the FS-client gave us.
        if($directory !== '' && substr($directory, strlen($directory)-1, 1) !== '/') {
          $directory .= '/';
        }
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
        // Path sanitization
        $remotefile = $this->normalizeRelativePath($remotefile);
        return app::getFilesystem()->isFile($this->normalizePath($remotefile));
    }

}
