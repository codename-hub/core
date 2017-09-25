<?php
namespace codename\core\validator\structure\api\codename\ssis;

/**
 * Validate a complete application object
 * @package core
 * @since 2016-11-08
 */
class appobject extends \codename\core\validator\structure\api\codename implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            '_token',
            '_time',
            'aid',
            'name',
            'url',
            'icon',
            'title',
    );

}
