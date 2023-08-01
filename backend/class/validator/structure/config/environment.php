<?php

namespace codename\core\validator\structure\config;

use codename\core\validator\structure\config;
use codename\core\validator\validatorInterface;

/**
 * Validating environment configurations
 * @package core
 * @since 2016-04-28
 */
class environment extends config implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'database',
      'mail',
      'cache',
      'filesystem',
    ];
}
