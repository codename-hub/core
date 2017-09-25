<?php
namespace codename\core\validator\structure\sepa;
use \codename\core\app;

/**
 * Validating SEPA XML Debitors
 * @package core
 * @since 2016-10-11
 */
class debitor extends \codename\core\validator\structure implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'debitor_name',
            'debitor_bic',
            'debitor_iban',
            'debitor_mandantid',
            'debitor_signaturedate'
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

        if(count($error = app::getValidator('text_bic')->validate($value['debitor_bic'])) > 0) {
            $this->errorstack->addError('VALUE', 'CONTAINING_BIC_IS_INVALID', $error);
            return $this->errorstack->getErrors();
        }

        if(count($error = app::getValidator('text_iban')->validate($value['debitor_iban'])) > 0) {
            $this->errorstack->addError('VALUE', 'CONTAINING_IBAN_IS_INVALID', $error);
            return $this->errorstack->getErrors();
        }

        if(count($error = app::getValidator('text_date')->validate($value['debitor_signaturedate']))) {
            $this->errorstack->addError('VALUE', 'CONTAINING_SEPAMANDATE_DATE_IS_INVALID', $error);
            return $this->errorstack->getErrors();
        }

        if(count($error = app::getValidator('text')->validate($value['debitor_mandantid']))) {
            $this->errorstack->addError('VALUE', 'CONTAINING_MANDANTID_DATE_IS_INVALID', $error);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

}
