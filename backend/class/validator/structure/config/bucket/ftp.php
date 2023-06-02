<?php

namespace codename\core\validator\structure\config\bucket;

use codename\core\app;
use codename\core\exception;
use codename\core\validator\structure\config\bucket;
use codename\core\validator\validatorInterface;
use ReflectionException;

class ftp extends bucket implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'basedir',
        // 'public',
      'ftpserver',
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
        if (count($this->errorstack->getErrors()) > 0) {
            return $this->errorstack->getErrors();
        }

        if (isset($value['public']) && !is_bool($value['public'])) {
            $this->errorstack->addError('VALUE', 'PUBLIC_KEY_INVALID');
            return $this->errorstack->getErrors();
        }

        if (isset($value['public']) && $value['public'] && !array_key_exists('baseurl', $value)) {
            $this->errorstack->addError('VALUE', 'BASEURL_NOT_FOUND');
            return $this->errorstack->getErrors();
        }

        if (count($errors = app::getValidator('structure_config_ftp')->reset()->validate($value['ftpserver'] ?? null)) > 0) {
            $this->errorstack->addError('VALUE', 'FTP_CONTAINER_INVALID', $errors);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }
}
