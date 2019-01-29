<?php
namespace codename\core\validator\text;

/**
 * I am a validiator for a HTTP(s) endpoints
 * @package core
 * @since 2016-11-10
 * @todo tear this validator apart from the protocol validation!
 */
class endpoint extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     * I am array of protocols that are allowed in the endpoint string
     * @var array
     */
    private $allowedProtocols = array('http', 'https');

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        parent::__CONSTRUCT($nullAllowed, 6, 128, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz://.:0123456789-');
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        parent::validate($value);

        if (count($this->errorstack->getErrors()) > 0) {
            return $this->errorstack->getErrors();
        }

        if(strpos($value, '://') === false) {
            $this->errorstack->addError('VALUE', 'NO_PROTOCOL_FOUND', $value);
            return $this->errorstack->getErrors();
        }

        if(!in_array(($protocol = explode(':', $value, 2)[0]), $this->allowedProtocols)) {
          $this->errorstack->addError('VALUE', 'PROTOCOL_NOT_ALLOWED', $protocol);
          return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

}
