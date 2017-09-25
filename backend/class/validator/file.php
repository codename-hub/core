<?php
namespace codename\core\validator;
use codename\core\app;

/**
 * Validating files
 * @package core
 * @since 2016-04-28
 */
class file extends \codename\core\validator implements \codename\core\validator\validatorInterface {

    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = array(
            "image/jpeg",
            "image/jpg",
            "image/png",
            "image/gif",
            "image/fif",
            "image/tiff",
            "image/vasa",
            "image/gif",
            "image/x-icon",
            "application/pdf"
    );

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        parent::validate($value);

        if(count($this->errorstack->getErrors()) > 0) {
            return $this->errorstack->getErrors();
        }

        if(!app::getFilesystem()->fileAvailable($value)) {
            $this->errorstack->addError('VALUE', 'FILE_NOT_FOUND', $value);
            return $this->errorstack->getErrors();
        }

        $mimetype = $this->getMimetype($value);
        if(!in_array($mimetype, $this->mime_whitelist)) {
            $this->errorstack->addError('VALUE', 'FORBIDDEN_MIME_TYPE', $mimetype);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

    /**
     * Returns the MIME type of the given file
     * @param string $file
     * @return string
     */
    protected function getMimetype(string $file) : string {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file);
        finfo_close($finfo);
        return $mimetype;
    }

    /**
     * Returns the uploaded file's ending by analyzing it's MIME type
     * @return string
     */
    public function getFiletype() : string {
        switch($this->getMimetype($this->upload['tmp_name'])) {
            case 'image/jpeg' : return 'jpg'; break;
            case 'image/gif' : return 'gif'; break;
            case 'image/png' : return 'png'; break;
            case 'image/fif' : return 'fif'; break;
            case 'image/ief' : return 'ief'; break;
            case 'image/tiff' : return 'tiff'; break;
            case 'image/vasa' : return 'vasa'; break;
            case 'image/x-icon' : return 'ico'; break;
            case 'application/pdf' : return 'pdf'; break;
            default: return 'fil'; break;
        }
    }

    /**
     * Returns the salted MD5 of the given file's checksum
     * @param string $salt
     * @return string
     */
    public function getMd5(string $salt = null) : string {
        return md5(md5_file($this->upload['tmp_name']) . $salt);
    }

}
