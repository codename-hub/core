<?php

namespace codename\core\validator\number;

use codename\core\validator\number;
use codename\core\validator\validatorInterface;

/**
 * Validating integers
 * @package core
 * @since 2017-05-31
 */
class integer extends number implements validatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function __construct(bool $nullAllowed = true, float $minvalue = null, float $maxvalue = null, int $maxprecision = null)
    {
        return parent::__construct($nullAllowed, $minvalue, $maxvalue, 0);
    }
}
