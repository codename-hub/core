<?php
namespace codename\core\validator\text;

/**
 * Timezone Validator for timezone declarations like:
 * Europe/Berlin
 * or
 * +0200
 */
class timezone extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        parent::__CONSTRUCT($nullAllowed, 3, 32, '0123456789+ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz/_-');
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate($value) : array
    {
      if(count(parent::validate($value)) > 0) {
        return $this->getErrors();
      }

      try {
        $dtz = new \DateTimeZone($value);

        if($dtz === false) {
          $this->errorstack->addError('VALUE', 'INVALID_TIMEZONE', $value);
        }
      } catch (\Exception $e) {
        $this->errorstack->addError('VALUE', 'INVALID_TIMEZONE', $value);
      }

      return $this->getErrors();
    }

}
