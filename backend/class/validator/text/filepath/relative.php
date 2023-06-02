<?php

namespace codename\core\validator\text\filepath;

use codename\core\validator\text;

class relative extends text
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, 1, 256, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789öäüßÖÄÜabcdefghijklmnopqrstuvwxyz_-./() ');
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

        if (str_starts_with($value, '/')) {
            $this->errorstack->addError('VALUE', 'MUST_NOT_BEGIN_WITH_SLASH', $value);
            return $this->errorstack->getErrors();
        }

        if (str_ends_with($value, '/')) {
            $this->errorstack->addError('VALUE', 'MUST_NOT_END_WITH_SLASH', $value);
            return $this->errorstack->getErrors();
        }


        return $this->errorstack->getErrors();
    }
}
