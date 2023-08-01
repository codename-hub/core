<?php

namespace codename\core\validator;

use codename\core\app;
use codename\core\exception;
use codename\core\validator;
use ReflectionException;

/**
 * Validating files
 * @package core
 * @since 2016-04-28
 */
class file extends validator implements validatorInterface
{
    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected array $mime_whitelist = [
      "image/jpeg",
      "image/jpg",
      "image/png",
      "image/gif",
      "image/fif",
      "image/tiff",
      "image/vasa",
      "image/gif",
      "image/x-icon",
      "application/pdf",
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

        if (!app::getFilesystem()->fileAvailable($value)) {
            $this->errorstack->addError('VALUE', 'FILE_NOT_FOUND', $value);
            return $this->errorstack->getErrors();
        }

        $mimetype = $this->getMimetype($value);
        if (!in_array($mimetype, $this->mime_whitelist)) {
            $this->errorstack->addError('VALUE', 'FORBIDDEN_MIME_TYPE', $mimetype);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

    /**
     * Returns the MIME type of the given file
     * @param string $file
     * @return string
     */
    protected function getMimetype(string $file): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file);
        finfo_close($finfo);
        return $mimetype;
    }
}
