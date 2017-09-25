<?php
namespace codename\core\validator\number;

use codename\core\validator;

/**
 * I will validate a port number
 * @package core
 * @since 2016-11-05
 */
class port extends \codename\core\validator\number implements \codename\core\validator\validatorInterface {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_number::__construct($nullAllowed, $minvalue, $maxvalue, $maxprecision)
     */
    public function __CONSTRUCT(bool $nullAllowed = true, float $minvalue = null, float $maxvalue = null, int $maxprecision = null) {
        return parent::__construct(true, 1, 65535, 0);
    }

}
