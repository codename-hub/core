<?php
namespace codename\core\validator\number;

/**
 * I can validate a numeric value as a payable money value
 * @package core
 * @since 2016-11-05
 */
class money extends \codename\core\validator\number implements \codename\core\validator\validatorInterface {


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

        // $value needs to be casted to a float, first.
        if(round($value, 2) !== (float)$value) {
            $this->errorstack->addError('VALUE', 'TOO_MANY_DIGITS_AFTER_COMMA', $value);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

}
