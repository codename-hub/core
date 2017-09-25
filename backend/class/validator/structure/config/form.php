<?php
namespace codename\core\validator\structure\config;

/**
 * Validating form configurations
 * @package core
 * @since 2016-04-28
 */
class form extends \codename\core\validator\structure\config implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'form_id',
            'form_action',
            'form_method'
    );
    
}
