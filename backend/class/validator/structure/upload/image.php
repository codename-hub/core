<?php

namespace codename\core\validator\structure\upload;

use codename\core\app;
use codename\core\exception;
use codename\core\validator\structure\upload;
use codename\core\validator\validatorInterface;
use ReflectionException;

/**
 * Validating uploaded images
 * @package core
 * @since 2016-04-28
 */
class image extends upload implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @param mixed $value
     * @return array
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate(mixed $value): array
    {
        parent::validate($value);

        if (count($this->errorstack->getErrors()) > 0) {
            return $this->errorstack->getErrors();
        }

        if (count($errors = app::getValidator('file_image')->reset()->validate($value['tmp_name'])) > 0) {
            $this->errorstack->addError('VALUE', 'IMAGE_INVALID', $errors);
            return $this->errorstack->getErrors();
        }
        return $this->errorstack->getErrors();
    }
}
