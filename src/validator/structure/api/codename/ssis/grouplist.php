<?php
namespace codename\core\validator\structure\api\codename\ssis;

use \codename\core\app;

/**
 * Validate a complete list of group elements
 * @package core
 * @since 2016-11-08
 */
class grouplist extends \codename\core\validator\structure\api\codename implements \codename\core\validator\validatorInterface {
    
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        parent::validate($value);
    
        if (count($value) == 0) {
            $this->errorstack->addError('VALUE', 'GROUPLIST_EMPTY', $value);
            return $this->errorstack->getErrors();
        }
        
        foreach($value as $key => $groupobject) {
            if(count($errors = app::getValidator('structure_api_codename_ssis_groupobject')->validate($groupobject)) > 0) {
                $this->errorstack->addError('VALUE', 'INVALID_GROUPOBJECT', $errors);
                return $this->errorstack->getErrors();
            }
        }

        return $this->errorstack->getErrors();
    }

}
