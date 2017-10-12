<?php
namespace codename\core\validator\text;

class domain extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     * @inheritDoc
     */
    public function __CONSTRUCT(bool $nullAllowed = false, int $minlength = 0, int $maxlength = 0, string $allowedchars = '', string $forbiddenchars = '' )
    {
      // @see https://stackoverflow.com/questions/32290167/what-is-the-maximum-length-of-a-dns-name
      $value = parent::__CONSTRUCT($nullAllowed, $minlength, 253, $allowedchars, $forbiddenchars);
      return $value;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        if(count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        if(strlen($value) == 0) {
            return $this->errorstack->getErrors();
        }

        $domainarr = explode('.', $value);

        if(count($domainarr) < 2) {
            $this->errorstack->addError('VALUE', 'NO_PERIOD_FOUND', $value);
            return $this->errorstack->getErrors();
        }

        if(gethostbyname($value) == $value) {
            $this->errorstack->addError('VALUE', 'DOMAIN_NOT_RESOLVED', $value);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

}
