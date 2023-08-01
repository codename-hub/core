<?php

namespace codename\core\validator\structure\config;

use codename\core\validator\structure\config;
use codename\core\validator\validatorInterface;

/**
 * Validating references from one model to another
 * @package core
 * @since 2016-04-28
 */
class modelreference extends config implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'model',
      'key',
      'display',
    ];
}
