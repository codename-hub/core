<?php
namespace codename\core\validator\text;

class json extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     * @param bool $nullAllowed
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        parent::__CONSTRUCT($nullAllowed, 0, 0, '', '');
        return $this;
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

        $data = json_decode($value);
        if(is_null($data)) {
            $this->errorstack->addError('VALUE', 'JSON_INVALID', $value);
        }

        return $this->errorstack->getErrors();
    }

}
