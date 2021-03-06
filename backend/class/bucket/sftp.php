<?php
namespace codename\core\bucket;
use \codename\core\app;
use codename\core\exception;
use codename\login\context\remote;

/**
 * I can manage files on a SFTP/SSH server.
 * Not a joke, though it's april fools day.
 * @package core
 * @since 2019-04-01
 */
class sftp extends \codename\core\bucket implements \codename\core\bucket\bucketInterface {

    /**
     * Contains the FTP connection stream
     * @var resource
     */
    protected $sshConnection = null;

    /**
     * [protected description]
     * @var resource
     */
    protected $connection = null;

    /**
     * Contains the public base URL to the webspace where files are located
     * @var string
     */
    public $baseurl = '';

    /**
     * transmission via SFTP (fopen/fwrite/fread)
     * @var string
     */
    const METHOD_SFTP = 'sftp';

    /**
     * transmission via SCP
     * @var string
     */
    const METHOD_SCP = 'scp';

    /**
     * method to use for transmission
     * @var [type]
     */
    protected $method = null;

    /**
     * Creates the instance, establishes the connection and authenticates
     * @param array $data
     * @return \codename\core\bucket
     */
    public function __construct(array $data) {
        parent::__construct($data);

        if(count($errors = app::getValidator('structure_config_bucket_sftp')->reset()->validate($data)) > 0) {
            $this->errorstack->addError('CONFIGURATION', 'CONFIGURATION_INVALID', $errors);
            throw new \codename\core\exception(self::EXCEPTION_CONSTRUCT_CONFIGURATIONINVALID, \codename\core\exception::$ERRORLEVEL_ERROR, $errors);
        }

        $this->basedir = $data['basedir'];

        $this->method = $data['sftp_method'] ?? self::METHOD_SFTP;

        $host = $data['sftpserver']['host'];
        $port = $data['sftpserver']['port'];

        try {
          $this->sshConnection = ssh2_connect($host, $port);
        } catch (\Exception $e) {
          throw new \codename\core\sensitiveException($e);
        }

        if(isset($data['public']) && $data['public']) {
            $this->baseurl = $data['baseurl'];
        }

        if(!$this->sshConnection) {
            $this->errorstack->addError('FILE', 'CONNECTION_FAILED', null);
            throw new exception('EXCEPTION_BUCKET_SFTP_SSH_CONNECTION_FAILED', exception::$ERRORLEVEL_ERROR, [ 'host' => $host ]);
            return $this;
        }

        if($data['sftpserver']['auth_type'] === 'password') {
          // TODO: key auth?
          $username = $data['sftpserver']['user'];
          $password = $data['sftpserver']['pass'];

          if (! @ssh2_auth_password($this->sshConnection, $username, $password)) {
              throw new exception("EXCEPTION_BUCKET_SFTP_SSH_AUTH_FAILED", exception::$ERRORLEVEL_ERROR);
          }
        } else if($data['sftpserver']['auth_type'] === 'pubkey_file') {
          $username = $data['sftpserver']['user'];

          $pubkeyString = $data['sftpserver']['pubkey'];
          $privkeyString = $data['sftpserver']['privkey'];

          $pubkeyFile = null;
          try {
            $pubkeyFile = tempnam('secure', 'sftp_pub_');
            $handle = fopen($pubkeyFile, 'w');
            fwrite($handle, $pubkeyString);
            fclose($handle);
          } catch (\Exception $e) {
            throw new exception('FILE_COULD_NOT_BE_WRITE', exception::$ERRORLEVEL_ERROR,array($pubkeyFile));
          }

          $privkeyFile = null;
          try {
            $privkeyFile = tempnam('secure', 'sftp_pri_');
            $handle = fopen($privkeyFile, 'w');
            fwrite($handle, $privkeyString);
            fclose($handle);
          } catch (\Exception $e) {
            throw new exception('FILE_COULD_NOT_BE_WRITE', exception::$ERRORLEVEL_ERROR,array($privkeyFile));
          }

          // TODO: passphrase for privkey file

          $passphrase = $data['sftpserver']['privkey_passphrase'] ?? null;
          if (! @ssh2_auth_pubkey_file($this->sshConnection, $username, $pubkeyFile, $privkeyFile, $passphrase)) {
            throw new exception("EXCEPTION_BUCKET_SFTP_SSH_AUTH_FAILED", exception::$ERRORLEVEL_ERROR);
          }

          // emulate "files" via streams?
        } else {
          throw new exception('EXCEPTION_BUCKET_SFTP_SSH_AUTH_TYPE_NOT_IMPLEMENTED', exception::$ERRORLEVEL_ERROR);
        }

        // initialize sftp client
        $this->connection = @ssh2_sftp($this->sshConnection);

        if(!$this->connection) {
          throw new exception("EXCEPTION_BUCKET_SFTP_SFTP_MODE_FAILED", exception::$ERRORLEVEL_ERROR);
        }

        // if(!@ftp_login($this->connection, $data['ftpserver']['user'], $data['ftpserver']['pass'])) {
        //     $this->errorstack->addError('FILE', 'LOGIN_FAILED', null);
        //     app::getLog('errormessage')->warning('CORE_BACKEND_CLASS_BUCKET_FTP_CONSTRUCT::LOGIN_FAILED ($user = ' . $data['ftpserver']['user'] .')');
        //     throw new exception('EXCEPTION_BUCKET_FTP_LOGIN_FAILED', exception::$ERRORLEVEL_ERROR, [ 'user' => $data['ftpserver']['user'] ]);
        //     return $this;
        // }

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

        if(!app::getFilesystem()->fileAvailable($localfile)) {
            $this->errorstack->addError('FILE', 'LOCAL_FILE_NOT_FOUND', $localfile);
            return false;
        }

        if($this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_EXISTS', $remotefile);
            return false;
        }

        $directory = $this->extractDirectory($remotefile);

        if(!$this->dirAvailable($directory)) {
            $this->dirCreate($directory);
        }

        //  @ftp_put($this->connection, $this->basedir . $remotefile, $localfile, FTP_BINARY);
        // \codename\core\app::getResponse()->setData('bucket_sftp_debug', [
        //   // $this->sshConnection,
        //   $localfile,
        //   $this->basedir,
        //   $remotefile
        // ]);


        if($this->method === self::METHOD_SFTP) {
          // Local stream
          if (!($localStream = @fopen($localfile, 'r'))) {
              throw new exception("Unable to open local file for reading: {$localfile}", exception::$ERRORLEVEL_ERROR);
          }
          // Remote stream
          if (!($remoteStream = fopen("ssh2.sftp://{$this->connection}/{$this->basedir}{$remotefile}", 'w'))) {
              throw new exception("Unable to open remote file for writing: {$this->basedir}{$remotefile}", exception::$ERRORLEVEL_ERROR);
          }
          // Write from our remote stream to our local stream
          $read = 0;
          $fileSize = filesize($localfile);
          while ($read < $fileSize && ($buffer = fread($localStream, $fileSize - $read))) {
              // Increase our bytes read
              $read += strlen($buffer);
              // Write to our local file
              if (fwrite($remoteStream, $buffer) === FALSE) {
                  throw new exception("Unable to write to local file: {$this->basedir}{$remotefile}", exception::$ERRORLEVEL_ERROR);
              }
          }
          // Close our streams
          fclose($localStream);
          fclose($remoteStream);

        } else if($this->method === self::METHOD_SCP) {

          //  TODO: provide create mode ?
          //  TODO: handle error ? FALSE return value?
          @ssh2_scp_send($this->sshConnection , $localfile, $this->basedir . $remotefile, 0777);

        } else {
          throw new exception('EXCEPTION_BUCKET_SFTP_INVALID_METHOD', exception::$ERRORLEVEL_ERROR, $this->method);
        }


        return $this->fileAvailable($remotefile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::filePull($remotefile, $localfile)
     */
    public function filePull(string $remotefile, string $localfile) : bool {
        // Path sanitization
        $remotefile = $this->normalizeRelativePath($remotefile);

        if(app::getFilesystem()->fileAvailable($localfile)) {
            $this->errorstack->addError('FILE', 'LOCAL_FILE_EXISTS', $localfile);
            return false;
        }

        if(!$this->fileAvailable($remotefile)) {
            $this->errorstack->addError('FILE', 'REMOTE_FILE_NOT_FOUND', $remotefile);
            return false;
        }

        //  @ftp_get($this->connection, $localfile, $this->basedir . $remotefile, FTP_BINARY

        // \codename\core\app::getResponse()->setData('bucket_sftp_debug', [
        //   // $this->sshConnection,
        //   $localfile,
        //   $this->basedir,
        //   $remotefile
        // ]);

        if($this->method === self::METHOD_SFTP) {
          // Remote stream
          if (!($remoteStream = @fopen("ssh2.sftp://{$this->connection}/{$this->basedir}{$remotefile}", 'r'))) {
              throw new exception("Unable to open remote file: {$this->basedir}{$remotefile}", exception::$ERRORLEVEL_ERROR);
          }
          // Local stream
          if (!($localStream = @fopen($localfile, 'w'))) {
              throw new exception("Unable to open local file for writing: {$localfile}", exception::$ERRORLEVEL_ERROR);
          }
          // Write from our remote stream to our local stream
          $read = 0;
          $fileSize = filesize("ssh2.sftp://{$this->connection}/{$this->basedir}{$remotefile}");
          while ($read < $fileSize && ($buffer = fread($remoteStream, $fileSize - $read))) {
              $bufferSize = strlen($buffer);
              // Increase our bytes read
              $read += $bufferSize;
              // Write to our local file
              $writtenBytes = fwrite($localStream, $buffer);
              // NOTE: fwrite does not return FALSE when not enough disk space
              if ($writtenBytes === FALSE || ($writtenBytes < $bufferSize)) {
                  throw new exception("Unable to write to local file: {$localfile}", exception::$ERRORLEVEL_ERROR);
              }
          }
          // Close our streams
          fclose($localStream);
          fclose($remoteStream);
        } else if($this->method === self::METHOD_SCP) {

          //  TODO: handle error ? FALSE return value?
          @ssh2_scp_recv($this->sshConnection, $this->basedir . $remotefile, $localfile);

        } else {
          throw new exception('EXCEPTION_BUCKET_SFTP_INVALID_METHOD', exception::$ERRORLEVEL_ERROR, $this->method);
        }

        return app::getFilesystem()->fileAvailable($localfile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::dirAvailable($directory)
     */
    public function dirAvailable(string $directory) : bool {
      // Path sanitization
      $directory = $this->normalizeRelativePath($directory);

      return $this->isDirectory($directory);
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

        $handle = @opendir("ssh2.sftp://{$this->connection}/{$this->basedir}{$directory}");
        if ($handle === false) {
          throw new exception("Unable to open remote directory", exception::$ERRORLEVEL_ERROR);
        }

        $files = array();

        $prefix = $directory != '' ? $directory.'/' : '';
        while (false !== ($entry = readdir($handle))) {
          // exclude current dir and parent
          if($entry != '.' && $entry != '..') {
            $files[] = $prefix.$entry;
          }
        }

        // close handle. otherwise, bad things happen.
        @closedir($handle);

        return $files;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket_interface::fileAvailable($remotefile)
     */
    public function fileAvailable(string $remotefile) : bool {
      // Path sanitization
      $remotefile = $this->normalizeRelativePath($remotefile);

      // CHANGED 2021-03-30: improved and fixed internal SFTP bucket handling
      return $this->isFile($remotefile);
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

        // @ftp_delete($this->connection, $this->basedir . $remotefile);
        // TODO: handle FALSE return value?
        @ssh2_sftp_unlink($this->connection, $this->basedir . $remotefile);

        return !$this->fileAvailable($remotefile);
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

      if(!$this->fileAvailable($remotefile)) {
          $this->errorstack->addError('FILE', 'REMOTE_FILE_NOT_FOUND', $remotefile);
          return false;
      }

      // check for existance of the new fileW
      if($this->fileAvailable($newremotefile)) {
          $this->errorstack->addError('FILE', 'FILE_ALREADY_EXISTS', $newremotefile);
          return false;
      }

      $targetDir = $this->extractDirectory($newremotefile);
      if(!$this->dirAvailable($targetDir)) {
        $this->dirCreate($targetDir);
      }

      // @ftp_rename($this->connection, $this->basedir . $remotefile, $this->basedir . $newremotefile);
      // TODO: handle FALSE return value?
      $success = @ssh2_sftp_rename($this->connection, $this->basedir . $remotefile, $this->basedir . $newremotefile);

      return $success && $this->fileAvailable($newremotefile);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket\bucketInterface::isFile()
     */
    public function isFile(string $remotefile) : bool {
      // Path sanitization
      $remotefile = $this->normalizeRelativePath($remotefile);

      $statResult = @ssh2_sftp_stat($this->connection, $this->basedir . $remotefile);
      if ($statResult === false) {
        return false;
      }

      //
      // check for dir
      // @see https://www.php.net/manual/en/function.stat.php#54999
      //
      if (self::S_IFDIR == ($statResult['mode'] & self::S_IFMT)) {
        return false;
      }

      return true; // ??
    }

    /**
     * [isDirectory description]
     * @param  string $directory [description]
     * @return bool              [description]
     */
    public function isDirectory(string $directory): bool {
      // Path sanitization
      $directory = $this->normalizeRelativePath($directory);

      $statResult = @ssh2_sftp_stat($this->connection, $this->basedir . $directory);
      if ($statResult === false) {
        return false;
      }

      //
      // check for dir
      // @see https://www.php.net/manual/en/function.stat.php#54999
      //
      if (self::S_IFDIR == ($statResult['mode'] & self::S_IFMT)) {
        return true;
      }

      return false;
    }

    /**
     * [S_IFMT description]
     * @see https://www.php.net/manual/en/function.stat.php#54999
     * @var int
     */
    const S_IFMT = 0170000;

    /**
     * [S_IFDIR description]
     * @see https://www.php.net/manual/en/function.stat.php#54999
     * @var int
     */
    const S_IFDIR = 040000;

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
     * @param string $directory
     * @return bool
     */
    public function dirCreate(string $directory) {
        // Path sanitization
        $directory = $this->normalizeRelativePath($directory);

        if($this->dirAvailable($directory)) {
            return true;
        }

        // @ftp_mkdir($this->connection, $directory);
        // TODO: handle errors (FALSE return value) and other params!
        try {
          @ssh2_sftp_mkdir($this->connection, $this->basedir.$directory, 0777, true);
        } catch (\Exception $e) {
          return false;
        }

        return $this->dirAvailable($directory);
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
