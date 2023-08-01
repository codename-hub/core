<?php

namespace codename\core\validator\structure\config\crud;

use codename\core\app;
use codename\core\exception;
use codename\core\validator\structure\config;
use codename\core\validator\validatorInterface;
use ReflectionException;

/**
 * Validating CRUD instance configurations
 * @package core
 * @since 2016-04-28
 */
class pagination extends config implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'limit',
    ];

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
        if (count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        if (is_null($value)) {
            return $this->errorstack->getErrors();
        }

        if (count($errors = app::getValidator('number_natural')->reset()->validate($value['limit'])) > 0) {
            $this->errorstack->addError('VALUE', 'INVALID_LIMIT', $errors);
            return $this->errorstack->getErrors();
        }

        if ($value['limit'] <= 0) {
            $this->errorstack->addError('VALUE', 'LIMIT_TOO_SMALL', $value['limit']);
            return $this->errorstack->getErrors();
        }

        if ($value['limit'] >= 501) {
            $this->errorstack->addError('VALUE', 'LIMIT_TOO_HIGH', $value['limit']);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }
}
