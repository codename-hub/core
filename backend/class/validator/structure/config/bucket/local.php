<?php
namespace codename\core\validator\structure\config\bucket;
use \codename\core\app;

class local extends \codename\core\validator\structure\config\bucket implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'basedir',
            'public'
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
        
        if(!is_bool($value['public'])) {
            $this->errorstack->addError('VALUE', 'PUBLIC_KEY_NOT_FOUND');
            return $this->errorstack->getErrors();
        }
        
        if($value['public'] && !array_key_exists('baseurl', $value)) {
            $this->errorstack->addError('VALUE', 'BASEURL_NOT_FOUND');
            return $this->errorstack->getErrors();
        }
        
        if(!app::getFilesystem()->dirAvailable($value['basedir'])) {
            $this->errorstack->addError('VALUE', 'DIRECTORY_NOT_FOUND', $value['basedir']);
            return $this->errorstack->getErrors();
        }
        
        return $this->errorstack->getErrors();
    }
    
}
