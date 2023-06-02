<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

class dummy extends text implements validatorInterface
{
    /**
     * @param bool $nullAllowed
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed);
        return $this;
    }
}
