<?php

namespace codename\core\validator\structure\config;

use codename\core\validator\structure\config;
use codename\core\validator\validatorInterface;

/**
 * Validating ftp connection configurators
 * @package core
 * @since 2016-05-18
 */
class ftp extends config implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'host',
      'port',
      'user',
      'pass',
    ];
}
