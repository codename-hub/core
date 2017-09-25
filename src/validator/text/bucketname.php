<?php
namespace codename\core\validator\text;

class bucketname extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        parent::__CONSTRUCT($nullAllowed, 1, 128, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-');
        return $this;
    }

}
