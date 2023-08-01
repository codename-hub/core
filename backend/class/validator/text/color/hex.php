<?php

namespace codename\core\validator\text\color;

use codename\core\validator\text\color;
use codename\core\validator\validatorInterface;

class hex extends color implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, 7, 7, '#0123456789ABCDEFabcdef');
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): array
    {
        if (count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        // HEX Color Regex
        // @see https://stackoverflow.com/questions/43706082/validation-hex-and-rgba-colors-using-regex-in-php
        // #[a-zA-Z0-9]{6}
        $regexp = '/^#[a-zA-Z0-9]{6}$/';
        $isValid = (bool)preg_match($regexp, $value);

        if ($isValid !== true) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_HEX_STRING', $value);
        }

        return $this->getErrors();
    }
}
