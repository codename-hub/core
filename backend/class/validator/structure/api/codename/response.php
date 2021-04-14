<?php
namespace codename\core\validator\structure\api\codename;
use \codename\core\app;

/**
 * Validate responses from codename API services
 * @package core
 * @since 2016-05-05
 */
class response extends \codename\core\validator\structure\api\codename implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'success',
            'data'
    );

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

        if(count($errors = app::getValidator('number_natural')->reset()->validate($value['success'])) > 0) {
            $this->errorstack->addError('VALUE', 'INVALID_SUCCESS_IDENTIFIER', $errors);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

}
