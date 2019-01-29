<?php
namespace codename\core\validator\text;

/**
 * Validator for hostnames
 * NOTE: this validator changed.
 * Per definition, hostnames are just a string of alphanumeric characters, dots and dashes.
 * Eventually, its only a 24-char string.
 *
 * We define the hostname as the DNS Name.
 *
 * Other functionality has been moved to the validator text_endpoint
 *
 * @package core
 * @since 2018-08-21
 */
class hostname extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        parent::__CONSTRUCT($nullAllowed, 1, 128, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.0123456789-');
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        parent::validate($value);
        return $this->errorstack->getErrors();
    }

}
