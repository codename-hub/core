<?php

namespace codename\core\validator\structure\config;

use codename\core\validator\structure\config;
use codename\core\validator\validatorInterface;

/**
 * Validating field configurations
 * @package core
 * @since 2016-04-28
 */
class field extends config implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'field_id',
      'field_validator',
      'field_name',
      'field_type',
      'field_value',
      'field_class',
      'field_required',
      'field_readonly',
      'field_title',
      'field_placeholder',
    ];
}
