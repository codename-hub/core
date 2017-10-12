<?php
namespace codename\core\validator\text;

class bic extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        parent::__CONSTRUCT($nullAllowed, '8', '11', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
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
      
      /**
       * @see https://github.com/ronanguilloux/IsoCodes/blob/master/src/IsoCodes/SwiftBic.php
       */
      $regexp = '/^([a-zA-Z]){4}([a-zA-Z]){2}([0-9a-zA-Z]){2}([0-9a-zA-Z]{3})?$/';
      $bicValid = (bool) preg_match($regexp, $value);

      if($bicValid !== true) {
        $this->errorstack->addError('VALUE', 'VALUE_NOT_A_BIC', $value);
      }

      return $this->errorstack->getErrors();
    }

}
