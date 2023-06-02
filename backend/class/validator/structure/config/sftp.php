<?php

namespace codename\core\validator\structure\config;

use codename\core\validator\structure\config;
use codename\core\validator\validatorInterface;

/**
 * Validating sftp connection configurators
 * @package core
 * @since 2019-04-01
 */
class sftp extends config implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'host',
      'port',
      'user',
        // 'pass'
    ];
}
