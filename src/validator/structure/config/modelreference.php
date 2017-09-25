<?php
namespace codename\core\validator\structure\config;

/**
 * Validating references from one model to another
 * @package core
 * @since 2016-04-28
 */
class modelreference extends \codename\core\validator\structure\config implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'model',
            'key',
            'display'
    );
    
}
