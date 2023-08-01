<?php

namespace codename\core\validator\structure\config\bucket;

use codename\core\validator\structure\config\bucket;
use codename\core\validator\validatorInterface;

/**
 * validator for configs for bucket driver s3
 */
class s3 extends bucket implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'bucket',
    ];

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate(mixed $value): array
    {
        parent::validate($value);

        return $this->errorstack->getErrors();
    }
}
