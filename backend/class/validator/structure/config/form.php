<?php

namespace codename\core\validator\structure\config;

use codename\core\validator\structure\config;
use codename\core\validator\validatorInterface;

/**
 * Validating form configurations
 * @package core
 * @since 2016-04-28
 */
class form extends config implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'form_id',
      'form_action',
      'form_method',
    ];
}
