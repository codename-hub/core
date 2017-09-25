<?php
namespace codename\core\filesystem;

/**
 * Handling files on the local filesystem
 * @package core
 * @since 2016-01-06
 */
class local extends \codename\core\filesystem implements \codename\core\filesystem\filesystemInterface {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\filesystem_interface::fileAvailable($file)
     */
    public function fileAvailable(string $file) : bool {
        return file_exists($file);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\filesystem_interface::fileDelete($file)
     */
    public function fileDelete(string $file) : bool {
        if(!$this->fileAvailable($file)) {
            return false;
        }

        if($this->isDirectory($file)) {
            return false;
        }

        @unlink($file);

        return !$this->fileAvailable($file);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\filesystem_interface::fileMove($source, $destination)
     */
    public function fileMove(string $source, string $destination) : bool {
        if(!$this->fileAvailable($source)) {
            $this->errorstack->addError('FILE', 'SOURCE_NOT_FOUND', $source);
            return false;
        }

        if($this->isDirectory($source)) {
            $this->errorstack->addError('FILE', 'SOURCE_IS_A_DIRECTORY', $source);
            return false;
        }

        if($this->fileAvailable($destination)) {
            $this->errorstack->addError('FILE', 'DESTINATION_ALREADY_EXISTS', $source);
            return false;
        }

        if(!$this->makePath($destination)) {
            $this->errorstack->addError('FILE', 'DESTINATION_PATH_NOT_CREATED', $destination);
            return false;
        }

        if(!$this->dirAvailable(dirname($destination))) {
            $this->errorstack->addError('FILE', 'DESTINATION_PATH_NOT_FOUND', $destination);
            return false;
        }

        if(!is_writable(dirname($destination))) {
            $this->errorstack->addError('FILE', 'DESTINATION_PATH_NOT_WRITABLE', $destination);
            return false;
        }

        @rename($source, $destination);

        return $this->fileAvailable($destination);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\filesystem_interface::fileCopy($source, $destination)
     */
    public function fileCopy(string $source, string $destination) : bool {
        if(!$this->fileAvailable($source)) {
            $this->errorstack->addError('FILE', 'SOURCE_NOT_FOUND', $source);
            return false;
        }

        if($this->isDirectory($source)) {
            $this->errorstack->addError('FILE', 'SOURCE_IS_A_DIRECTORY', $source);
            return false;
        }

        if($this->fileAvailable($destination)) {
            $this->errorstack->addError('FILE', 'DESTINATION_ALREADY_EXISTS', $source);
            return false;
        }

        if(!$this->makePath($destination)) {
            $this->errorstack->addError('FILE', 'DESTINATION_PATH_NOT_CREATED', $destination);
            return false;
        }

        @copy($source, $destination);

        return $this->fileAvailable($destination);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\filesystem_interface::fileRead($file)
     */
    public function fileRead(string $file) : string {
        if(!$this->fileAvailable($file)) {
            return false;
        }

        if($this->isDirectory($file)) {
            $this->errorstack->addError('FILE', 'DESTINATION_IS_A_DIRECTORY', $file);
            return '';
        }

        return file_get_contents($file);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\filesystem_interface::fileWrite($file, $content)
     */
    public function fileWrite(string $file, string $content=null) : bool {
        if(!$this->makePath($file)) {
            $this->errorstack->addError('FILE', 'DESTINATION_PATH_NOT_CREATED', $file);
            return false;
        }

        if($this->fileAvailable($file)) {
            $this->errorstack->addError('FILE', 'FILE_ALREADY_EXISTS', $file);
            return false;
        }

        @file_put_contents($file, $content);

        return $this->fileAvailable($file);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\filesystem_interface::getInfo($file)
     */
    public function getInfo(string $file) : array {
      return array(
        'filesize' => filesize($file),
        'filectime' => filectime($file),
      );
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\filesystem_interface::dirAvailable($directory)
     */
    public function dirAvailable(string $directory) : bool {
        if(!$this->fileAvailable($directory)) {
            return false;
        }
        return $this->isDirectory($directory);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\filesystem_interface::isDirectory($path)
     */
    public function isDirectory(string $path) : bool {
        if(!$this->fileAvailable($path)) {
            return false;
        }
        return (bool) @is_dir($path);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\filesystem\filesystemInterface::isFile()
     */
    public function isFile(string $file) : bool {
        if(!$this->fileAvailable($file)) {
            return false;
        }
        return @is_file($file);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\filesystem_interface::dirCreate($directory)
     */
    public function dirCreate(string $directory) : bool {
        if($this->fileAvailable($directory)) {
            return false;
        }

        @mkdir($directory);
        return $this->fileAvailable($directory);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\filesystem_interface::dirList($directory)
     */
    public function dirList(string $directory) : array {
        if(!$this->isDirectory($directory)) {
            return array();
        }
        $list = scandir($directory);

        $myList = array();
        foreach($list as $object) {
            if($object == '.' || $object == '..') {
                continue;
            }
            $myList[] = $object;
        }

        return $myList;
    }

    /**
     * My create several directories until the complete path is available.
     * @param string $directory
     * @return bool
     */
    protected function makePath(string $directory) : bool {
        $folders = explode('/', $directory);
        array_pop($folders);
        $myDir = '/';
        foreach ($folders as $folder) {
            $myDir = $myDir . '/' . $folder;
            if($this->dirAvailable($myDir)) {
                continue;
            }

            if(!$this->dirCreate($myDir)) {
                return false;
            }
        }
        return true;
    }

}
