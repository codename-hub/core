<?php
namespace codename\core\validator;

/**
 * Validate numbers
 * @package core
 * @since 2016-02-04
 */
class number extends \codename\core\validator implements \codename\core\validator\validatorInterface {
    
    /**
     * I may hold a minimum value to validate against
     * @var unknown
     */
    private $minvalue;
    
    /**
     * I may hold a maximum value for the number
     * @var unknown
     */
    private $maxvalue;
    
    /**
     * I may hold the maximum amount of decimal places
     * @var unknown
     */
    private $maxprecision;

    /**
     * Creates a numeric validator with the given option
     * @param bool $nullAllowed
     * @param float $minvalue [Used to check if the value is too low]
     * @param float $maxvalue [Used to check if the value is too high]
     * @param int $maxprecision [Used to check if the value has too many decimal places]
     * @return \codename\core\validator\number
     */
    public function __CONSTRUCT(bool $nullAllowed = true, float $minvalue = null, float $maxvalue = null, int $maxprecision = null) {
        $this->nullAllowed = $nullAllowed;
        $this->minvalue = $minvalue;
        $this->maxvalue = $maxvalue;
        $this->maxprecision = $maxprecision;
        $this->errorstack = new \codename\core\errorstack('VALIDATION');
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        parent::validate($value);
        
        if(!is_numeric($value)) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_A_NUMBER', $value);
            return $this->errorstack->getErrors();
        }
        
        if(!is_null($this->minvalue) && $value < $this->minvalue) {
            $this->errorstack->addError('VALUE', 'VALUE_TOO_SMALL', $value);
            return $this->errorstack->getErrors();
        }
        
        if(!is_null($this->maxvalue) && $value > $this->maxvalue) {
            $this->errorstack->addError('VALUE', 'VALUE_TOO_BIG', array('VAL' => $value, 'MAX' => $this->maxvalue));
            return $this->errorstack->getErrors();
        }
        
        if(!is_null($this->maxprecision) && round($value, $this->maxprecision) != $value) {
            $this->errorstack->addError('VALUE', 'VALUE_TOO_PRECISE', $value);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }
    
}
