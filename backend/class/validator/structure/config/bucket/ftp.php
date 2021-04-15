<?php
namespace codename\core\validator\structure\config\bucket;
use \codename\core\app;

class ftp extends \codename\core\validator\structure\config\bucket implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'basedir',
            // 'public',
            'ftpserver'
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

        if(isset($value['public']) && !is_bool($value['public'])) {
            $this->errorstack->addError('VALUE', 'PUBLIC_KEY_INVALID');
            return $this->errorstack->getErrors();
        }

        if(isset($value['public']) && $value['public'] && !array_key_exists('baseurl', $value)) {
            $this->errorstack->addError('VALUE', 'BASEURL_NOT_FOUND');
            return $this->errorstack->getErrors();
        }

        if(count($errors = app::getValidator('structure_config_ftp')->reset()->validate($value['ftpserver'])) > 0) {
            $this->errorstack->addError('VALUE', 'FTP_CONTAINER_INVALID', $errors);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

}
