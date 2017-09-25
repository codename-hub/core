<?php
namespace codename\core\validator\structure\sepa;
use \codename\core\app;

/**
 * Validating SEPA XML Export constructor information
 * @package core
 * @since 2016-10-11
 */
class export extends \codename\core\validator\structure implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'company_name',
            'company_iban',
            'company_bic',
            'company_sepagid'
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

        if(count($error = app::getValidator('text_bic')->validate($value['company_bic'])) > 0) {
            $this->errorstack->addError('VALUE', 'CONTAINING_BIC_IS_INVALID', $error);
            return $this->errorstack->getErrors();
        }
        if(count($error = app::getValidator('text_iban')->validate($value['company_iban'])) > 0) {
            $this->errorstack->addError('VALUE', 'CONTAINING_IBAN_IS_INVALID', $error);
            return $this->errorstack->getErrors();
        }
        if(!is_string($value['company_sepagid']) || strlen($value['company_sepagid']) == 0) {
            $this->errorstack->addError('VALUE', 'CONTAINING_SEPAGID_IS_INVALID', null);
            return $this->errorstack->getErrors();
        }
        if(!is_string($value['company_name']) || strlen($value['company_name']) == 0) {
            $this->errorstack->addError('VALUE', 'CONTAINING_COMPANYNAME_IS_INVALID', null);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

}
