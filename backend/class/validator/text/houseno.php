<?php
namespace codename\core\validator\text;

class houseno extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

  /**
   * {@inheritDoc}
   * @see \codename\core\validator_text::__construct($nullAllowed)
   */
  public function __CONSTRUCT(bool $nullAllowed = false) {
    parent::__CONSTRUCT($nullAllowed, 1, 32, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-/');
    return $this;
  }

  /**
   * {@inheritDoc}
   * @see \codename\core\validator_interface::validate($value)
   */
  public function validate($value) : array {
    if(count(parent::validate($value)) != 0) {
        return $this->errorstack->getErrors();
    }

    if (!preg_match('/^[1-9](([0-9]{0,4}[a-zA-Z]?)?((\/|-)[0-9]{0,4}[a-zA-Z]?)?){0,1}$/i',$value)) {
      $this->errorstack->addError('VALUE', 'HOUSENO_INVALID', $value);
      return $this->errorstack->getErrors();
    }

    return $this->errorstack->getErrors();
  }

}
