<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

class mac extends text implements validatorInterface
{
    /**
     * @param bool $nullAllowed
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, 17, 17, '0123456789ABCDEF:');
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate(mixed $value): array
    {
        if (count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        if (!filter_var($value, FILTER_VALIDATE_MAC)) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_A_MACADDRESS', $value);
        }

        return $this->errorstack->getErrors();
    }
}
