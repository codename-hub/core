<?php

namespace codename\core\validator\structure\api\codename\ssis;

use codename\core\validator\structure\api\codename;
use codename\core\validator\validatorInterface;

/**
 * Validate a complete group object
 * @package core
 * @since 2016-11-08
 */
class groupobject extends codename implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      '_token',
      '_time',
      'name',
      'gid',
    ];
}
