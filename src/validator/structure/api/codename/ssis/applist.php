<?php
namespace codename\core\validator\structure\api\codename\ssis;

use \codename\core\app;

/**
 * Validate a complete list of application elements
 * @package core
 * @since 2016-11-08
 */
class applist extends \codename\core\validator\structure\api\codename implements \codename\core\validator\validatorInterface {
    
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        parent::validate($value);
    
        if (count($value) == 0) {
            $this->errorstack->addError('VALUE', 'APPLIST_EMPTY', $value);
            return $this->errorstack->getErrors();
        }
        
        foreach($value as $key => $appobject) {
            if(count($errors = app::getValidator('structure_api_codename_ssis_appobject')->validate($appobject)) > 0) {
                $this->errorstack->addError('VALUE', 'INVALID_APPOBJECT', $errors);
                return $this->errorstack->getErrors();
            }
        }

        return $this->errorstack->getErrors();
    }

}
