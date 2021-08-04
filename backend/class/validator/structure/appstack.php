<?php
namespace codename\core\validator\structure;

/**
 * Validating appstack arrays
 * @package core
 * @since 2016-04-28
 */
class appstack extends \codename\core\validator\structure implements \codename\core\validator\validatorInterface {

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

        if (count($value) == 0) {
            $this->errorstack->addError('VALUE', 'APPSTACK_EMPTY', $value);
        }

        return $this->errorstack->getErrors();
    }
    
}
