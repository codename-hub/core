<?php
namespace codename\core\validator\structure\api\codename\ssis;

use \codename\core\app;

/**
 * Validate a complete session object that is returned from the SSIS API
 * @package core
 * @since 2016-11-08
 */
class sessionobject extends \codename\core\validator\structure\api\codename implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            '_token',
            '_time',
            'user',
            'group',
            'app'
    );
    
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        parent::validate($value);
    
        if (count($value) == 0) {
            $this->errorstack->addError('VALUE', 'APPSTACK_EMPTY', $value);
            return $this->errorstack->getErrors();
        }
        
        if(count($errors = app::getValidator('structure_api_codename_ssis_userobject')->validate($value['user'])) > 0) {
            $this->errorstack->addError('VALUE', 'INVALID_USEROBJET', $errors);
            return $this->errorstack->getErrors();
        }

        if(count($errors = app::getValidator('structure_api_codename_ssis_grouplist')->validate($value['group'])) > 0) {
            $this->errorstack->addError('VALUE', 'INVALID_GROUPLIST', $errors);
            return $this->errorstack->getErrors();
        }

        if(count($errors = app::getValidator('structure_api_codename_ssis_applist')->validate($value['app'])) > 0) {
            $this->errorstack->addError('VALUE', 'INVALID_APPLIST', $errors);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

}
