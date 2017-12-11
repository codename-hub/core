<?php
namespace codename\core\validator\boolean;

/**
 * Validating files
 * @package core
 * @since 2016-08-03
 */
class number extends \codename\core\validator implements \codename\core\validator\validatorInterface {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        parent::validate($value);

        if(is_numeric($value) || is_integer($value)) {
          if($value != 0 && $value != 1) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_NUMERIC_BOOLEAN');
            return $this->errorstack->getErrors();
          } else {
            return $this->errorstack->getErrors();
          }
        }

        if(!is_bool($value)) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_BOOLEAN');
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

}
