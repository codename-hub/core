<?php
namespace codename\core\validator\structure\config\crud;

use codename\core\app;

/**
 * Validating CRUD instance configurations
 * @package core
 * @since 2016-04-28
 */
class pagination extends \codename\core\validator\structure\config implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'limit'
    );

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        if(count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        if(is_null($value)) {
            return $this->errorstack->getErrors();
        }

        if(count($errors = app::getValidator('number_natural')->reset()->validate($value['limit'])) > 0) {
            $this->errorstack->addError('VALUE', 'INVALID_LIMIT', $errors);
            return $this->errorstack->getErrors();
        }
        
        if($value['limit'] <= 0) {
            $this->errorstack->addError('VALUE', 'LIMIT_TOO_SMALL', $value['limit']);
            return $this->errorstack->getErrors();
        }
        
        if($value['limit'] >= 501) {
            $this->errorstack->addError('VALUE', 'LIMIT_TOO_HIGH', $value['limit']);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }
    
}
