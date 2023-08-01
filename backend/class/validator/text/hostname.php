<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

/**
 * Validator for hostnames
 * NOTE: this validator changed.
 * Per definition, hostnames are just a string of alphanumeric characters, dots and dashes.
 * Eventually, it's only a 24-char string.
 *
 * We define the hostname as the DNS Name.
 *
 * Other functionality has been moved to the validator text_endpoint
 *
 * @package core
 * @since 2018-08-21
 */
class hostname extends text implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, 1, 128, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.0123456789-');
        return $this;
    }

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
