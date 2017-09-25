<?php
namespace codename\core\validator\structure\config\crud;

/**
 * Validating CRUD instance configurations
 * @package core
 * @since 2016-04-28
 */
class action extends \codename\core\validator\structure\config\crud implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'name',
            'view',
            'context',
            'icon',
            'btnClass'
    );

    /**
     * @todo DOCUMENTATION
     */
    public function validate($value) : array {
        $this->checkKeys($value);

        return $this->errorstack->getErrors();
    }
    
}
