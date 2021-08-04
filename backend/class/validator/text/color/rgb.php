<?php
namespace codename\core\validator\text\color;

class rgb extends \codename\core\validator\text\color implements \codename\core\validator\validatorInterface {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        parent::__CONSTRUCT($nullAllowed, 10, 19, 'rgb(); ,0123456789');
        return $this;
        // shortest string: rgb(0,1,2)
        // longest string: rgb(255, 255, 255);
    }

    /**
     * @inheritDoc
     */
    public function validate($value) : array
    {
      if(count(parent::validate($value)) != 0) {
        return $this->errorstack->getErrors();
      }

      // RGB Regex
      // @see https://stackoverflow.com/questions/43706082/validation-hex-and-rgba-colors-using-regex-in-php
      // rgb\((?:\s*\d+\s*,){2}\s*[\d]+\)
      $regexp = '/^rgb\((?:\s*\d+\s*,){2}\s*[\d]+\)$/';
      $isValid = (bool) preg_match($regexp, $value);

      if($isValid !== true) {
        $this->errorstack->addError('VALUE', 'VALUE_NOT_RGB_STRING', $value);
      }

      return $this->getErrors();
    }

}
