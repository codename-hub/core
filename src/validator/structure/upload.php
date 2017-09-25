<?php
namespace codename\core\validator\structure;

/**
 * Validating uploads
 * @package core
 * @since 2016-04-28
 */
class upload extends \codename\core\validator\structure implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'name',
            'type',
            'tmp_name',
            'size'
    );

}
