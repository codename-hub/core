<?php
namespace codename\core\validator\structure\config\bucket;

/**
 * validator for configs for bucket driver s3
 */
class s3 extends \codename\core\validator\structure\config\bucket implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
      'bucket',
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
        return $this->errorstack->getErrors();
    }

}
