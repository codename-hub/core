<?php
namespace codename\core\validator\text;

class domain extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        if(count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }
        
        if(strlen($value) == 0) {
            return $this->errorstack->getErrors();
        }
        
        $domainarr = explode('.', $value);

        if(count($domainarr) < 2) {
            $this->errorstack->addError('VALUE', 'NO_PERIOD_FOUND', $value);
            return $this->errorstack->getErrors();
        }
        
        if(gethostbyname($value) == $value) {
            $this->errorstack->addError('VALUE', 'DOMAIN_NOT_RESOLVED', $value);
            return $this->errorstack->getErrors();
        }
        
        return $this->errorstack->getErrors();
    }

}
