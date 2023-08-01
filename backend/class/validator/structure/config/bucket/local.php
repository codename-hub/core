<?php

namespace codename\core\validator\structure\config\bucket;

use codename\core\app;
use codename\core\exception;
use codename\core\validator\structure\config\bucket;
use codename\core\validator\validatorInterface;
use ReflectionException;

class local extends bucket implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'basedir',
      'public',
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

        if (isset($value['public']) && !is_bool($value['public'])) {
            $this->errorstack->addError('VALUE', 'PUBLIC_KEY_NOT_FOUND');
            return $this->errorstack->getErrors();
        }

        if (isset($value['public']) && $value['public'] && !array_key_exists('baseurl', $value)) {
            $this->errorstack->addError('VALUE', 'BASEURL_NOT_FOUND');
            return $this->errorstack->getErrors();
        }

        if (!app::getFilesystem()->dirAvailable($value['basedir'])) {
            $this->errorstack->addError('VALUE', 'DIRECTORY_NOT_FOUND', $value['basedir']);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }
}
