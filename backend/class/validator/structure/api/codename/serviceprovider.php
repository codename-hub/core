<?php

namespace codename\core\validator\structure\api\codename;

use codename\core\app;
use codename\core\exception;
use codename\core\validator\structure\api\codename;
use codename\core\validator\validatorInterface;
use ReflectionException;

/**
 * Validate responses from codename API services
 * @package core
 * @since 2016-11-08
 */
class serviceprovider extends codename implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'host',
      'port',
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

        if (count($errors = app::getValidator('text_endpoint')->reset()->validate($value['host'])) > 0) {
            $this->errorstack->addError('VALUE', 'HOST_INVALID', $errors);
        }

        if (count($errors = app::getValidator('number_port')->reset()->validate($value['port'])) > 0) {
            $this->errorstack->addError('VALUE', 'PORT_INVALID', $errors);
        }

        return $this->errorstack->getErrors();
    }
}
