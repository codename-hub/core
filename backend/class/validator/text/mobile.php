<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

class mobile extends text implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed)
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, 1, 32, '01234567890+ -()/');
        return $this;
    }
}
