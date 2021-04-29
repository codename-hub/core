<?php
namespace codename\core\validator\text\filepath;

class absolute extends \codename\core\validator\text {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
          parent::__CONSTRUCT($nullAllowed, 1, 256, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789öäüßÖÄÜabcdefghijklmnopqrstuvwxyz_-./()\: ');
        } else {
          parent::__CONSTRUCT($nullAllowed, 1, 256, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789öäüßÖÄÜabcdefghijklmnopqrstuvwxyz_-./() ');
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {

        $this->nullAllowed = true;
        if(count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        if(strpos($value, '/') != 0) {
            $this->errorstack->addError('VALUE', 'MUST_BEGIN_WITH_SLASH', $value);
            return $this->errorstack->getErrors();
        }

        if(substr($value, -1) === '/') {
            $this->errorstack->addError('VALUE', 'MUST_NOT_END_WITH_SLASH', $value);
            return $this->errorstack->getErrors();
        }


        return $this->errorstack->getErrors();
    }

}
