<?php

namespace codename\core\validator\structure;

use codename\core\validator\structure;
use codename\core\validator\validatorInterface;

/**
 * Validating uploads
 * @package core
 * @since 2016-04-28
 */
class upload extends structure implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'name',
      'type',
      'tmp_name',
      'size',
    ];
}
