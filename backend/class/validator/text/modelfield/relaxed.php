<?php

namespace codename\core\validator\text\modelfield;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

/**
 * validator allowing relaxed rules on field naming
 * for working with existing, 3rd party databases
 */
class relaxed extends text implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __construct(bool $nullAllowed = false)
    {
        //
        // NOTE: we simply set 128 as an arbitrary limit for key lengths
        //
        parent::__construct($nullAllowed, 1, 128, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_.0123456789');
        return $this;
    }
}
