<?php
namespace codename\core\validator\text;

class methodname extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __CONSTRUCT(bool $nullAllowed = true) {
        parent::__CONSTRUCT(true, 3, 32, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890_', '');
        return $this;
    }
    
}
