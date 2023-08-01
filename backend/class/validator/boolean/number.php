<?php

namespace codename\core\validator\boolean;

use codename\core\validator;
use codename\core\validator\validatorInterface;

/**
 * Validating files
 * @package core
 * @since 2016-08-03
 */
class number extends validator implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate(mixed $value): array
    {
        parent::validate($value);

        if (is_numeric($value) || is_integer($value)) {
            if ($value != 0 && $value != 1) {
                $this->errorstack->addError('VALUE', 'VALUE_NOT_NUMERIC_BOOLEAN');
            }
            return $this->errorstack->getErrors();
        }

        if (!is_bool($value)) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_BOOLEAN');
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }
}
