<?php

namespace codename\core\validator\structure\api\codename;

use codename\core\validator\structure\api\codename;
use codename\core\validator\validatorInterface;

/**
 * Validating the example API client
 * @package core
 * @since 2016-04-28
 */
class example extends codename implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'host',
      'port',
      'app',
      'secret',
    ];
}
