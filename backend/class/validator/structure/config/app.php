<?php
namespace codename\core\validator\structure\config;

/**
 * Validating application configurations
 * @package core
 * @since 2016-04-28
 */
class app extends \codename\core\validator\structure\config implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'context',
            'defaultcontext',
            'defaulttemplate'
    );

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        parent::validate($value);

        foreach($value['context'] as $context) {
            if(count($errors = \codename\core\app::getValidator('structure_config_context')->validate($context)) > 0) {
                $this->errorstack->addError('VALUE', 'CORE_BACKEND_CLASS_VALIDATOR_structure_config_APP', $errors);
            }
        }
        
        if(count($errors = \codename\core\app::getValidator('text_templatename')->validate($value['defaulttemplate'])) > 0) {
            $this->errorstack->addError('VALUE', 'CORE_BACKEND_CLASS_VALIDATOR_structure_config_APP', $errors);
        }
        
        if(count($errors = \codename\core\app::getValidator('text_appname')->validate($value['defaultcontext'])) > 0) {
            $this->errorstack->addError('VALUE', 'CORE_BACKEND_CLASS_VALIDATOR_structure_config_APP', $errors);
        }
        
        return $this->errorstack->getErrors();
    }
    
}
