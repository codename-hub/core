<?php
namespace codename\core\validator\text;

class username extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     * 
     * @param bool $nullAllowed
     * @return \codename\core\validator\text\username
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        parent::__CONSTRUCT($nullAllowed, 5, 10, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        return $this;
    }

}
