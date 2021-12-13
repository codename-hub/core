<?php
namespace codename\core\validator\structure\upload;

use \codename\core\app;

/**
 * Validating uploaded images
 * @package core
 * @since 2016-04-28
 */
class image extends \codename\core\validator\structure\upload implements \codename\core\validator\validatorInterface {

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

        if(count($errors = app::getValidator('file_image')->reset()->validate($value['tmp_name'])) > 0) {
            $this->errorstack->addError('VALUE', 'IMAGE_INVALID', $errors);
            return $this->errorstack->getErrors();
        }
        return $this->errorstack->getErrors();
    }

}
