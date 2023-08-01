<?php

namespace codename\core\validator\structure\api\codename\ssis;

use codename\core\app;
use codename\core\exception;
use codename\core\validator\structure\api\codename;
use codename\core\validator\validatorInterface;
use ReflectionException;

/**
 * Validate a complete session object that is returned from the SSIS API
 * @package core
 * @since 2016-11-08
 */
class sessionobject extends codename implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      '_token',
      '_time',
      'user',
      'group',
      'app',
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
        parent::validate($value);

        if (count($value) == 0) {
            $this->errorstack->addError('VALUE', 'APPSTACK_EMPTY', $value);
            return $this->errorstack->getErrors();
        }

        if (count($errors = app::getValidator('structure_api_codename_ssis_userobject')->validate($value['user'])) > 0) {
            $this->errorstack->addError('VALUE', 'INVALID_USEROBJET', $errors);
            return $this->errorstack->getErrors();
        }

        if (count($errors = app::getValidator('structure_api_codename_ssis_grouplist')->validate($value['group'])) > 0) {
            $this->errorstack->addError('VALUE', 'INVALID_GROUPLIST', $errors);
            return $this->errorstack->getErrors();
        }

        if (count($errors = app::getValidator('structure_api_codename_ssis_applist')->validate($value['app'])) > 0) {
            $this->errorstack->addError('VALUE', 'INVALID_APPLIST', $errors);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }
}
