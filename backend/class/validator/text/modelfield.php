<?php
namespace codename\core\validator\text;

class modelfield extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        //
        // NOTE: 64 chars is the MySQL column name length limit
        //
        parent::__CONSTRUCT($nullAllowed, 3, 128, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_.0123456789');
        return $this;
    }

}
