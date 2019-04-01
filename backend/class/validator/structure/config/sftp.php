<?php
namespace codename\core\validator\structure\config;

/**
 * Validating sftp connection configurators
 * @package core
 * @since 2019-04-01
 */
class sftp extends \codename\core\validator\structure\config implements \codename\core\validator\validatorInterface {

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
