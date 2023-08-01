<?php

namespace codename\core\validator\structure;

use codename\core\validator\structure;
use codename\core\validator\validatorInterface;

class product extends structure implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'product_id',
      'product_count',
      'product_price',
    ];
}
