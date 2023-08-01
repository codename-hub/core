<?php

namespace codename\core\validator\number;

use codename\core\validator\number;
use codename\core\validator\validatorInterface;

/**
 * I will validate a port number
 * @package core
 * @since 2016-11-05
 */
class port extends number implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_number::__construct($nullAllowed, $minvalue, $maxvalue, $maxprecision)
     */
    public function __construct(bool $nullAllowed = true, float $minvalue = null, float $maxvalue = null, int $maxprecision = null)
    {
        return parent::__construct(true, 1, 65535, 0);
    }
}
