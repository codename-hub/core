<?php
namespace codename\core\validator\text\modelfield;

/**
 * validator allowing relaxed rules on field naming
 * for working with existing, 3rd party databases
 */
class relaxed extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        //
        // NOTE: we simply set 128 as an arbitrary limit for key lengths
        //
        parent::__CONSTRUCT($nullAllowed, 1, 128, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_.0123456789');
        return $this;
    }

}
