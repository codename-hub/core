<?php

namespace codename\core\validator\structure\config;

use codename\core\app;
use codename\core\exception;
use codename\core\validator\structure\config;
use codename\core\validator\validatorInterface;
use ReflectionException;

/**
 * Validating model configurations
 * @package core
 * @since 2016-04-28
 */
class model extends config implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'field',
      'primary',
      'datatype',
    ];

    /**
     * {@inheritDoc}
     * @param mixed $value
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    public function validate(mixed $value): array
    {
        if (count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        if (is_null($value)) {
            return $this->errorstack->getErrors();
        }

        // check field names
        if (!empty($value['field'])) {
            if (!is_array($value['field'])) {
                $this->errorstack->addError('VALUE', 'KEY_FIELD_NOT_A_ARRAY', $value);
                return $this->errorstack->getErrors();
            }
            if (!is_array($value['datatype'])) {
                $this->errorstack->addError('VALUE', 'KEY_DATATYPE_NOT_A_ARRAY', $value);
                return $this->errorstack->getErrors();
            }

            foreach ($value['field'] as $field) {
                // validate modelfield
                if (count($errors = app::getValidator('text_modelfield')->reset()->validate($field)) > 0) {
                    $this->errorstack->addError('VALUE', 'KEY_FIELD_INVALID', $errors);
                    return $this->errorstack->getErrors();
                } elseif (!array_key_exists($field, $value['datatype'])) {
                    // validate datatype config existence AND its validity
                    $this->errorstack->addError('VALUE', 'DATATYPE_CONFIG_MISSING', $field);
                } else {
                    // validate datatype?
                }
            }
        }

        // check primary key existence
        // we expect an array!
        if (!empty($value['primary'])) {
            if (!is_array($value['primary'])) {
                $this->errorstack->addError('VALUE', 'KEY_PRIMARY_NOT_A_ARRAY', $value);
                return $this->errorstack->getErrors();
            }

            foreach ($value['primary'] as $primary) {
                if (!in_array($primary, $value['field'])) {
                    $this->errorstack->addError('VALUE', 'PRIMARY_KEY_NOT_CONTAINED_IN_FIELD_ARRAY', $primary);
                }
            }
        }

        return $this->getErrors();
    }
}
