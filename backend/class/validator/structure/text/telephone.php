<?php
namespace codename\core\validator\structure\text;

use codename\core\app;

/**
 * validator that validates multiple telephone numbers at once
 */
class telephone extends \codename\core\validator\structure
{
  /**
   * [protected description]
   * @var \codename\core\validator
   */
  protected $elementValidator = null;

  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(bool $nullAllowed = true)
  {
    parent::__CONSTRUCT($nullAllowed);
    $this->elementValidator = app::getValidator('text_telephone');
  }

  /**
   * @inheritDoc
   */
  public function validate($value) : array
  {
    if(count(parent::validate($value)) != 0) {
        return $this->errorstack->getErrors();
    }

    if(is_null($value)) {
        return $this->errorstack->getErrors();
    }

    if(is_array($value)) {
      foreach($value as $phoneNumber) {
        if(count($errors = $this->elementValidator->reset()->validate($phoneNumber)) > 0) {
          $this->errorstack->addError('VALUE', 'INVALID_PHONE_NUMBER', $errors);
        }
      }
    }

    return $this->getErrors();
  }
}
