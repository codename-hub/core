<?php
namespace codename\core\validator\structure\config;

/**
 * Validating model filtering configs
 * @package core
 * @since 2016-07-19
 */
class modelfilter extends \codename\core\validator\structure implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'field',
            'value',
            'operator'
    );
    
}
