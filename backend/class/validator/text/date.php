<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

class date extends text implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, 10, 10, '0123456789-');
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate(mixed $value): array
    {
        $this->nullAllowed = true;
        if (count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        $datearr = explode('-', $value);

        if (count($datearr) != 3) {
            $this->errorstack->addError('VALUE', 'INVALID_COUNT_AREAS', $value);
            return $this->errorstack->getErrors();
        }

        // search invalid characters
        if (strlen($datearr[0]) != 4) {
            $this->errorstack->addError('VALUE', 'INVALID_YEAR', $value);
            return $this->errorstack->getErrors();
        }

        // search invalid characters
        if (strlen($datearr[1]) != 2) {
            $this->errorstack->addError('VALUE', 'INVALID_MONTH', $value);
            return $this->errorstack->getErrors();
        }

        if (!checkdate($datearr[1], $datearr[2], $datearr[0])) {
            $this->errorstack->addError('VALUE', 'INVALID_DATE', $value);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }
}
