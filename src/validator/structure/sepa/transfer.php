<?php
namespace codename\core\validator\structure\sepa;

/**
 * Validating SEPA XML Creditors
 * @package core
 * @since 2016-10-11
 */
class transfer extends \codename\core\validator\structure implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
        'creditor',
        'value',
        'text'
    );

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        parent::validate($value);

        if(count($this->errorstack->getErrors()) > 0) {
            return $this->errorstack->getErrors();
        }

        if(!is_object($value['creditor'])) {
            $this->errorstack->addError('VALUE', 'CONTAINING_CREDITOR_NOT_AN_OBJECT', $value);
            return $this->errorstack->getErrors();
        }

        if(!$value['creditor'] instanceof \codename\core\value\structure\sepa\creditor) {
            $this->errorstack->addError('VALUE', 'CONTAINING_CREDITOR_NOT_AN_INSTANCE_OF_CREDITOR_VALUE_OBJECT', $value);
            return $this->errorstack->getErrors();
        }

        if(!is_numeric($value['value'])){
            $this->errorstack->addError('VALUE', 'CONTAINING_VALUE_NOT_NUMERIC', $value);
            return $this->errorstack->getErrors();
        }

        if($value['value'] <= 0) {
            $this->errorstack->addError('VALUE', 'CONTAINING_VALUE_ZERO_OR_BELOW', $value);
            return $this->errorstack->getErrors();
        }

        if(!is_string($value['text'])) {
            $this->errorstack->addError('VALUE', 'CONTAINING_TEXT_NOT_STRING', $value);
            return $this->errorstack->getErrors();
        }

        if(strlen($value['text']) == 0) {
            $this->errorstack->addError('VALUE', 'CONTAINING_TEXT_EMPTY', $value);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

}
