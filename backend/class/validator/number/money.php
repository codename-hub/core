<?php

namespace codename\core\validator\number;

use codename\core\validator\number;
use codename\core\validator\validatorInterface;

/**
 * I can validate a numeric value as a payable money value
 * @package core
 * @since 2016-11-05
 */
class money extends number implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate(mixed $value): array
    {
        parent::validate($value);

        return $this->errorstack->getErrors();
    }
}
