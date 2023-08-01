<?php

namespace codename\core;

use codename\core\validator\validatorInterface;

/**
 * Validate everything!
 * @package core
 * @since 2016-01-23
 */
class validator implements validatorInterface
{
    /**
     * Holds true if the value can be null
     * @var bool $nullAllowed
     */
    protected bool $nullAllowed;

    /**
     * Contains the errors as instance of \codename\core\errorstack
     * @var errorstack
     */
    protected errorstack $errorstack;

    /**
     * @param bool $nullAllowed
     */
    public function __construct(bool $nullAllowed = true)
    {
        $this->errorstack = new errorstack('VALIDATION');
        $this->nullAllowed = $nullAllowed;
        return $this;
    }

    /**
     * Performs validation and directly returns the state of validation (true/false)
     * @param mixed|null $value
     * @return bool
     */
    final public function isValid(mixed $value): bool
    {
        return (count($this->validate($value)) == 0);
    }

    /**
     *
     * {@inheritDoc}
     * @see validator_interface::validate
     */
    public function validate(mixed $value): array
    {
        if (is_null($value) && !$this->nullAllowed) {
            $this->errorstack->addError('VALIDATOR', 'VALUE_IS_NULL');
        }
        return $this->getErrors();
    }

    /**
     * Returns the errors that occurred during validation of this value
     * @return array
     */
    final public function getErrors(): array
    {
        return $this->errorstack->getErrors();
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): validator
    {
        $this->errorstack->reset();
        return $this;
    }
}
