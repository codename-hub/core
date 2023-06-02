<?php

namespace codename\core;

use codename\core\errorstack\errorstackInterface;
use JsonSerializable;

/**
 * The errorstack is a collector for all errors that might occur in other classes.
 * @package core
 * @since 2016-03-11
 * @todo Use the class \codename\core\datacontainer
 */
class errorstack implements errorstackInterface, JsonSerializable
{
    /**
     * Contains all the errors in this stack
     * @var array
     */
    protected array $errors = [];

    /**
     * Contains the type of the errors in this stack
     * @var string $type
     */
    protected string $type = 'error';

    /**
     * Contains an action that will be executed when an error is added
     * @var callable|null
     */
    protected $callback = null;

    /**
     * Creates the errorstack instance
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = strtoupper($type);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see errorstack_interface::addError, $code, $detail)
     */
    final public function addError(string $identifier, string $code, mixed $detail = null): errorstack
    {
        $this->errors[] = [
          '__IDENTIFIER' => $identifier,
          '__CODE' => $this->type . '.' . $code,
          '__TYPE' => $this->type,
          '__DETAILS' => $detail,
        ];

        if (is_array($this->callback)) {
            call_user_func([$this->callback['object'], $this->callback['function']], $this->getErrors());
        }

        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see errorstack_interface::getErrors
     */
    final public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * {@inheritDoc}
     */
    public function addErrorstack(errorstack $errorstack): errorstack
    {
        $this->addErrors($errorstack->getErrors());
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addErrors(array $errors): errorstack
    {
        foreach ($errors as $error) {
            $this->errors[] = $error;
        }
        if (is_array($this->callback)) {
            call_user_func([$this->callback['object'], $this->callback['function']], $this->getErrors());
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see errorstack_interface::isSuccess
     */
    final public function isSuccess(): bool
    {
        return (count($this->getErrors()) == 0);
    }

    /**
     * Adds a callback for errors.
     * Add the $object and the $function of the object that will be called
     * @param object $object
     * @param string $function
     */
    final public function setCallback(object $object, string $function): void
    {
        $this->callback = [
          'object' => $object,
          'function' => $function,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): errorstack
    {
        $this->errors = [];
        return $this;
    }

    /**
     * {@inheritDoc}
     * custom serialization
     */
    public function jsonSerialize(): mixed
    {
        return $this->getErrors();
    }
}
