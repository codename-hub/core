<?php

namespace codename\core\validator\structure\text;

use codename\core\app;
use codename\core\exception;
use codename\core\validator;
use codename\core\validator\structure;
use ReflectionException;

/**
 * validator that validates multiple telephone numbers at once
 */
class telephone extends structure
{
    /**
     * [protected description]
     * @var validator
     */
    protected validator $elementValidator;

    /**
     * {@inheritDoc}
     * @param bool $nullAllowed
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(bool $nullAllowed = true)
    {
        parent::__construct($nullAllowed);
        $this->elementValidator = app::getValidator('text_telephone');
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): array
    {
        if (count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        if (is_null($value)) {
            return $this->errorstack->getErrors();
        }

        if (is_array($value)) {
            foreach ($value as $phoneNumber) {
                if (count($errors = $this->elementValidator->reset()->validate($phoneNumber)) > 0) {
                    $this->errorstack->addError('VALUE', 'INVALID_PHONE_NUMBER', $errors);
                }
            }
        }

        return $this->getErrors();
    }
}
