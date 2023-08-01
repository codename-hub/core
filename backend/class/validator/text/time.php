<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

class time extends text implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, 1, 8, '0123456789:');
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): array
    {
        if (count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        $c = explode(':', $value);
        if (count($c) >= 2 && count($c) <= 3) {
            $hours = $c[0];
            $minutes = $c[1];
            $seconds = $c[2] ?? 0;
            if ($hours < 0 || $hours > 23) {
                $this->errorstack->addError('VALUE', 'VALUE_INVALID_TIME_HOURS', $value);
            }
            if ($minutes < 0 || $minutes > 59) {
                $this->errorstack->addError('VALUE', 'VALUE_INVALID_TIME_MINUTES', $value);
            }
            if ($seconds < 0 || $seconds > 59) {
                $this->errorstack->addError('VALUE', 'VALUE_INVALID_TIME_SECONDS', $value);
            }
        } else {
            $this->errorstack->addError('VALUE', 'VALUE_INVALID_TIME_STRING', $value);
        }
        return $this->errorstack->getErrors();
    }
}
