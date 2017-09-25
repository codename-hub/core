<?php
namespace codename\core\validator\structure\config;

/**
 * Validating ftp connection configurators
 * @package core
 * @since 2016-05-18
 */
class ftp extends \codename\core\validator\structure\config implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'host',
            'port',
            'user',
            'pass'
    );
    
}
