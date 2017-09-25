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
        parent::validate($value);

        if(count($this->errorstack->getErrors()) > 0) {
            return $this->errorstack->getErrors();
        }

        foreach($value as $product) {
            if(count($errors = app::getValidator('structure_product')->validate($product)) > 0) {
                $this->errorstack->addError('VALUE', 'INVALID_PRODUCT_FOUND', $errors);
            }
        }

        return $this->errorstack->getErrors();
    }

}
