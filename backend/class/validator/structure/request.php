<?php

namespace codename\core\validator\structure;

use codename\core\validator\structure;
use codename\core\validator\validatorInterface;

class request extends structure implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'context',
      'view',
    ];
}
