<?php
namespace codename\core\validator\structure\config;

/**
 * Validating environment configurations
 * @package core
 * @since 2016-04-28
 */
class environment extends \codename\core\validator\structure\config implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'database',
            'mail',
            'cache',
            'filesystem'
    );
    
}
