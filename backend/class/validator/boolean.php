<?php

namespace codename\core\validator;

use codename\core\validator;

/**
 * Validating files
 * @package core
 * @since 2016-08-03
 */
class boolean extends validator implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate(mixed $value): array
    {
        parent::validate($value);

        if (!is_bool($value)) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_BOOLEAN');
        }

        return $this->errorstack->getErrors();
    }
}
