<?php
namespace codename\core\validator\structure\config;

/**
 * Validating model ordering configs
 * @package core
 * @since 2016-04-28
 */
class modelorder extends \codename\core\validator\structure implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'field',
            'direction'
    );
    
}
