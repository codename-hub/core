<?php

namespace codename\core\validator\structure\config;

use codename\core\validator\structure;
use codename\core\validator\validatorInterface;

/**
 * Validating model ordering configs
 * @package core
 * @since 2016-04-28
 */
class modelorder extends structure implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'field',
      'direction',
    ];
}
