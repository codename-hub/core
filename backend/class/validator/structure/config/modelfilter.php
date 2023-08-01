<?php

namespace codename\core\validator\structure\config;

use codename\core\validator\structure;
use codename\core\validator\validatorInterface;

/**
 * Validating model filtering configs
 * @package core
 * @since 2016-07-19
 */
class modelfilter extends structure implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'field',
      'value',
      'operator',
    ];
}
