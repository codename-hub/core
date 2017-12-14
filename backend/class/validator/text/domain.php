<?php
namespace codename\core\validator\text;

class domain extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     * @inheritDoc
     */
    public function __CONSTRUCT(bool $nullAllowed = false, int $minlength = 0, int $maxlength = 0, string $allowedchars = '', string $forbiddenchars = '' )
    {
      // @see https://stackoverflow.com/questions/32290167/what-is-the-maximum-length-of-a-dns-name
      // @see https://blogs.msdn.microsoft.com/oldnewthing/20120412-00/?p=7873
      // Invalid if:
      // - Is longer than 255 octets.
      // - Contains a label longer than 63 octets.

      // we declare:
      // a minimum of 4 chars (e.g. g.cn - one of the shortest known domain names)
      // a maximum of 253 chars (@TODO: check 63-char limit on labels)
      $value = parent::__CONSTRUCT($nullAllowed, 4, 253, $allowedchars, '/:');
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
