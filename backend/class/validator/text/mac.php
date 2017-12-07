<?php
namespace codename\core\validator\text;

class mac extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     * @param bool $nullAllowed
     */
    public function __CONSTRUCT(bool $nullAllowed) {
        parent::__CONSTRUCT($nullAllowed, 17, 17, '0123456789ABCDEF:', '');
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        parent::validate($value);

        if(!filter_var($value, FILTER_VALIDATE_MAC)) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_A_MACADDRESS', $value);
        }

        return $this->errorstack->getErrors();
    }

}
