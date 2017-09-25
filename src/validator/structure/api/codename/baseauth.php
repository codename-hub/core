<?php
namespace codename\core\validator\structure\api\codename;

/**
 * Validate basic authentications for a API request
 * @package core
 * @since 2016-11-08
 */
class baseauth extends \codename\core\validator\structure\api\codename implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'app_name',
            'app_secret'
    );

}
