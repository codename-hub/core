<?php
namespace codename\core\validator\text\datetime;

/**
 * relative datetime validator
 * for validating values like
 * +4 weeks
 * yesterday
 * -1 month
 */
class relative extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

  /**
   * @inheritDoc
   */
  public function validate($value) : array
  {
    if(count(parent::validate($value)) != 0) {
      return $this->errorstack->getErrors();
    }
    try {
      $dtObj = new \DateTime($value);
    } catch (\Exception $e) {
      $this->errorstack->addError('VALUE', 'INVALID_RELATIVE_DATETIME', $value);
    }
    return $this->errorstack->getErrors();
  }

}
