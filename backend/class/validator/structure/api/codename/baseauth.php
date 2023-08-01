<?php

namespace codename\core\validator\structure\api\codename;

use codename\core\validator\structure\api\codename;
use codename\core\validator\validatorInterface;

/**
 * Validate basic authentications for an API request
 * @package core
 * @since 2016-11-08
 */
class baseauth extends codename implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'app_name',
      'app_secret',
    ];
}
