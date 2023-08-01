<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

class username extends text implements validatorInterface
{
    /**
     *
     * @param bool $nullAllowed
     * @return username
     */
    public function __construct(bool $nullAllowed = false)
    {
        parent::__construct($nullAllowed, 5, 10, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        return $this;
    }
}
