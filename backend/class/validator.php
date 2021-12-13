<?php
namespace codename\core;

/**
 * Validate everything!
 * @package core
 * @since 2016-01-23
 */
class validator implements \codename\core\validator\validatorInterface {

    /**
     * Contains the value
     * @var unknown $value
     */
    protected $value = null;

    /**
     * Holds true if the value can be null
     * @var bool $nullAllowed
     */
    protected $nullAllowed = null;

    /**
     * Contains the errors as instance of \codename\core\errorstack
     * @var \codename\core\errorstack
     */
    protected $errorstack = null;

    /**
     * @param bool $nullAllowed
     */
    public function __CONSTRUCT(bool $nullAllowed = true) {
        $this->errorstack = new \codename\core\errorstack('VALIDATION');
        $this->nullAllowed = $nullAllowed;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        if (is_null($value) && !$this->nullAllowed) {
            $this->errorstack->addError('VALIDATOR', 'VALUE_IS_NULL');
        }
        return $this->getErrors();
    }

    /**
     * Returns the errors that occured during validation of this value
     * @return array
     */
    final public function getErrors() : array {
        return $this->errorstack->getErrors();
    }

    /**
     * Performs validation and directly returns the state of validation (true/false)
     * @param mixed|null $value
     * @return bool
     */
    final public function isValid($value) : bool {
        return (count($this->validate($value)) == 0);
    }

    /**
     * @inheritDoc
     */
    public function reset() : \codename\core\validator
    {
      $this->errorstack->reset();
      return $this;
    }
}
