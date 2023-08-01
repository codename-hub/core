<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

class filename extends text implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, 4, 128, '0123456789.abcdefghijklmnopqrstuvwxyzöäüßÖÄÜABCDEFGHIJKLMNOPQRSTUVWXYZ_-() ');
        return $this;
    }
}
