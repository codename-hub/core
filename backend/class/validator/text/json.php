<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

class json extends text implements validatorInterface
{
    /**
     * @param bool $nullAllowed
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed);
        return $this;
    }

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

        if (strlen($value) == 0) {
            return $this->errorstack->getErrors();
        }

        $data = json_decode($value);
        if (is_null($data)) {
            $this->errorstack->addError('VALUE', 'JSON_INVALID', $value);
        }

        return $this->errorstack->getErrors();
    }
}
