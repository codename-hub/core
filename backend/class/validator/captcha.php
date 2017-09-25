<?php
namespace codename\core\validator;

/**
 * validate texts for length, (in)valid characters and regular expressions
 * @package core
 * @since 2016-02-04
 */
class captcha extends \codename\core\validator\text {

  /**
   * @inheritDoc
   */
  public function validate($value): array
  {
    parent::validate($value);

    $cap = new \codename\core\captcha\simplecaptcha();
    if(!$cap->validate($value)) {
      $this->errorstack->addError('VALUE', 'WRONG_CAPTCHA', $value);
    }
    return $this->errorstack->getErrors();
  }

}
