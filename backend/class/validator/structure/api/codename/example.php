<?php
namespace codename\core\validator\structure\api\codename;

/**
 * Validating the example API client
 * @package core
 * @since 2016-04-28
 */
class example extends \codename\core\validator\structure\api\codename implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'host',
            'port',
            'app',
            'secret'
    );

}
