<?php

namespace codename\core\validator\text\color;

use codename\core\validator\text\color;
use codename\core\validator\validatorInterface;

class rgba extends color implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, 13, 27, 'rgba(); ,.0123456789');
        return $this;
        // shortest string: rgba(0,1,2,0)
        // longest string: rgba(255, 255, 255, 1.000);
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): array
    {
        if (count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        // RGBA Regex
        // @see https://stackoverflow.com/questions/43706082/validation-hex-and-rgba-colors-using-regex-in-php
        // but this was wrong, spaces after the last comma caused mis-validation
        // rgba\((\s*\d+\s*,\s*){3}[\d\.]+\)
        $regexp = '/^rgba\((\s*\d+\s*,\s*){3}[\d\.]+\)$/';
        $isValid = (bool)preg_match($regexp, $value);

        if ($isValid !== true) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_RGBA_STRING', [
              '$value' => $value,
              '$isValid' => $isValid,
            ]);
        }

        return $this->getErrors();
    }
}
