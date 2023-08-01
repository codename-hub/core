<?php

namespace codename\core\validator\structure\config\crud;

use codename\core\validator\structure\config\crud;
use codename\core\validator\validatorInterface;

/**
 * Validating CRUD instance configurations
 * @package core
 * @since 2016-04-28
 */
class action extends crud implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'name',
      'view',
      'context',
      'icon',
      'btnClass',
    ];

    /**
     *
     * {@inheritDoc}
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

        $this->checkKeys($value);

        return $this->errorstack->getErrors();
    }
}
