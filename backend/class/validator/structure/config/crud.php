<?php
namespace codename\core\validator\structure\config;
use \codename\core\app;

/**
 * Validating CRUD instance configurations
 * @package core
 * @since 2016-04-28
 */
class crud extends \codename\core\validator\structure\config implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'pagination',
            'visibleFields',
            'order'
    );

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        if(count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        if(is_null($value)) {
            return $this->errorstack->getErrors();
        }

        if(count($errors = app::getValidator('structure_config_crud_pagination')->reset()->validate($value['pagination'])) > 0) {
            $this->errorstack->addError('VALUE', 'PAGINATION_CONFIGURATION_INVALID', $errors);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }
    
}
