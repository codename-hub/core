<?php

namespace codename\core\validator\structure;

use codename\core\validator\structure;
use codename\core\validator\validatorInterface;

/**
 * Validating address arrays
 * @package core
 * @since 2016-04-28
 */
class address extends structure implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'country_id',
      'postalcode',
      'city',
      'street',
      'number',
    ];
}
