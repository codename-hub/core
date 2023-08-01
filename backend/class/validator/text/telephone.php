<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

class telephone extends text implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed)
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, 1, 32, '0123456789+ -()/');
        return $this;
    }
}
