<?php

namespace codename\core\validator\structure\api\codename\ssis;

use codename\core\app;
use codename\core\exception;
use codename\core\validator\structure\api\codename;
use codename\core\validator\validatorInterface;
use ReflectionException;

/**
 * Validate a complete list of application elements
 * @package core
 * @since 2016-11-08
 */
class applist extends codename implements validatorInterface
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

        if (count($value) == 0) {
            $this->errorstack->addError('VALUE', 'APPLIST_EMPTY', $value);
            return $this->errorstack->getErrors();
        }

        foreach ($value as $appobject) {
            if (count($errors = app::getValidator('structure_api_codename_ssis_appobject')->validate($appobject)) > 0) {
                $this->errorstack->addError('VALUE', 'INVALID_APPOBJECT', $errors);
                return $this->errorstack->getErrors();
            }
        }

        return $this->errorstack->getErrors();
    }
}
