<?php
namespace codename\core\validator\text;

class ipv4 extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     * @param bool $nullAllowed
     */
    public function __CONSTRUCT($nullAllowed = false) {
        parent::__CONSTRUCT($nullAllowed, 7, 15, '0123456789.', '');
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        if(count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }
        
        if(!filter_var($value, FILTER_VALIDATE_IP)) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_AN_IP', $value);
        }
        
        return $this->errorstack->getErrors();
    }
    
}
