<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

class bic extends text implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, '11', '11', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
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

        /**
         * @see https://github.com/ronanguilloux/IsoCodes/blob/master/src/IsoCodes/SwiftBic.php
         */
        $regexp = '/^([a-zA-Z]){4}([a-zA-Z]){2}([0-9a-zA-Z]){2}([0-9a-zA-Z]){3}?$/';
        $bicValid = (bool)preg_match($regexp, $value);

        if ($bicValid !== true) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_A_BIC', $value);
        }

        return $this->errorstack->getErrors();
    }
}
