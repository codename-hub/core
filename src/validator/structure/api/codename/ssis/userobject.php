<?php
namespace codename\core\validator\structure\api\codename\ssis;

use \codename\core\app;

/**
 * Validate a complete session object that is returned from the SSIS API
 * @package core
 * @since 2016-11-08
 */
class userobject extends \codename\core\validator\structure\api\codename implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            '_token',
            '_time',
            'firstname',
            'lastname',
            'displayname',
            'email',
            'username',
            'profilephoto',
            'uid'
    );
    
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function vaslidate($value) : array {
        parent::validate($value);
    
        if (count($value) == 0) {
            $this->errorstack->addError('VALUE', 'APPSTACK_EMPTY', $value);
            return $this->errorstack->getErrors();
        }
        
        if(count($errors = app::getValidator('structure_api_codename_ssis_userobject')) > 0) {
            $this->errorstack->addError('VALUE', 'INVALID_USEROBJECT', $errors);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

}
