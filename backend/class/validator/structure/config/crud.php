<?php

namespace codename\core\validator\structure\config;

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
class crud extends config implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'pagination',
      'visibleFields',
      'order',
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

        if (count($errors = app::getValidator('structure_config_crud_pagination')->reset()->validate($value['pagination'] ?? null)) > 0) {
            $this->errorstack->addError('VALUE', 'PAGINATION_CONFIGURATION_INVALID', $errors);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }
}
