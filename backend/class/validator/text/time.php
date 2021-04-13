<?php
namespace codename\core\validator\text;

class time extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        parent::__CONSTRUCT($nullAllowed, 1, 8, '0123456789:');
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate($value): array
    {
      if(count(parent::validate($value)) != 0) {
        return $this->errorstack->getErrors();
      }

      $c = explode(':', $value);
      if(count($c) >= 2 && count($c) <= 3) {
        $hours = $c[0];
        $minutes = $c[1];
        $seconds = $c[2] ?? 0;
        if($hours < 0 || $hours > 23) {
          $this->errorstack->addError('VALUE', 'VALUE_INVALID_TIME_HOURS', $value);
        }
        if($minutes < 0 || $minutes > 59) {
          $this->errorstack->addError('VALUE', 'VALUE_INVALID_TIME_MINUTES', $value);
        }
        if($seconds < 0 || $seconds > 59) {
          $this->errorstack->addError('VALUE', 'VALUE_INVALID_TIME_SECONDS', $value);
        }
      } else {
        $this->errorstack->addError('VALUE', 'VALUE_INVALID_TIME_STRING', $value);
      }
      return $this->errorstack->getErrors();
    }

}
