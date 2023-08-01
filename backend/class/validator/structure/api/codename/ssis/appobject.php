<?php

namespace codename\core\validator\structure\api\codename\ssis;

use codename\core\validator\structure\api\codename;
use codename\core\validator\validatorInterface;

/**
 * Validate a complete application object
 * @package core
 * @since 2016-11-08
 */
class appobject extends codename implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      '_token',
      '_time',
      'aid',
      'name',
      'url',
      'icon',
      'title',
    ];
}
