<?php
namespace codename\core\validator\structure;
use \codename\core\app;

class cart extends \codename\core\validator\structure implements \codename\core\validator\validatorInterface {

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

        foreach($value as $product) {
            if(count($errors = app::getValidator('structure_product')->validate($product)) > 0) {
                $this->errorstack->addError('VALUE', 'INVALID_PRODUCT_FOUND', $errors);
                break;
            }
        }

        return $this->errorstack->getErrors();
    }

}
