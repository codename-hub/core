<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

class ipv4 extends text implements validatorInterface
{
    /**
     * @param bool $nullAllowed
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, 7, 15, '0123456789.');
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

        if (!filter_var($value, FILTER_VALIDATE_IP)) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_AN_IP', $value);
        }

        return $this->errorstack->getErrors();
    }
}
