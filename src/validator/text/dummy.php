<?php
namespace codename\core\validator\text;

class dummy extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     * @param bool $nullAllowed
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        parent::__CONSTRUCT($nullAllowed, 0, 0, '', '');
        return $this;
    }


}
