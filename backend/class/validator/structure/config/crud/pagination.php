<?php
namespace codename\core\validator\structure\config\crud;

/**
 * Validating CRUD instance configurations
 * @package core
 * @since 2016-04-28
 */
class pagination extends \codename\core\validator\structure\config\crud implements \codename\core\validator\validatorInterface {

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
        parent::validate($value);
        
        if(count($this->errorstack->getErrors()) > 0) {
            return $this->errorstack->getErrors();
        }
        
        if(count(app::getValidator('number_natural')->validate($value['limit'])) > 0) {
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
