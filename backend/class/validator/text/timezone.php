<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;
use DateTimeZone;
use Exception;

/**
 * Timezone Validator for timezone declarations like:
 * Europe/Berlin
 * or
 * +0200
 */
class timezone extends text implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, 3, 32, '0123456789+ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz/_-');
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): array
    {
        if (count(parent::validate($value)) > 0) {
            return $this->getErrors();
        }

        try {
            new DateTimeZone($value);
        } catch (Exception) {
            $this->errorstack->addError('VALUE', 'INVALID_TIMEZONE', $value);
        }

        return $this->getErrors();
    }
}
