<?php

namespace codename\core\validator\text\datetime;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;
use DateTime;
use Exception;

/**
 * relative datetime validator
 * for validating values like
 * +4 weeks
 * yesterday
 * -1 month
 */
class relative extends text implements validatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): array
    {
        if (count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }
        try {
            new DateTime($value);
        } catch (Exception) {
            $this->errorstack->addError('VALUE', 'INVALID_RELATIVE_DATETIME', $value);
        }
        return $this->errorstack->getErrors();
    }
}
