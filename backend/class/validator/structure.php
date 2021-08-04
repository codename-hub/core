<?php
namespace codename\core\validator;

/**
 * Validate arrays
 * @package core
 * @since 2016-02-04
 */
class structure extends \codename\core\validator implements \codename\core\validator\validatorInterface {
    
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array();

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
        
        if (!is_array($value)) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_A_ARRAY', $value);
            return $this->errorstack->getErrors();
        }
        
        $this->checkKeys($value);

        return $this->errorstack->getErrors();
    }

    /**
     * I will use the $arrKeys property of the validator_array class (that I am) to check if all keys exist in the value
     * @param array $value
     * @return void
     */
    protected function checkKeys(array $value) {
        foreach ($this->arrKeys as $myKey) {
            if(strlen($myKey) > 0 && !array_key_exists($myKey, $value)) {
                $this->errorstack->addError('VALUE', 'ARRAY_MISSING_KEY', array('value'=>$value, 'key' => $myKey));
            }
        }
        return;
    }
    
}
