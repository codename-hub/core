<?php
namespace codename\core;

/**
 * The errorstack is a collector for all errors that might occur in other classes.
 * @package core
 * @since 2016-03-11
 * @todo Use the class \codename\core\datacontainer
 */
class errorstack implements \codename\core\errorstack\errorstackInterface, \JsonSerializable {

    /**
     * Contains all the errors in this stack
     * @var array $client
     */
    protected $errors = array();

    /**
     * Contains the type of the errors in this stack
     * @var unknown $type
     */
    protected $type = 'error';

    /**
     * Contains an action that will be executed when an error is added
     * @var function
     */
    protected $callback = null;

    /**
     * Creates the errorstack instance
     * @param string $type
     */
    public function __construct(string $type) {
        $this->type = strtoupper($type);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\errorstack_interface::addError($identifier, $code, $detail)
     */
    final public function addError(string $identifier, string $code, $detail = null) : \codename\core\errorstack {
        array_push($this->errors, array(
                '__IDENTIFIER' => $identifier,
                '__CODE' => "{$this->type}.{$code}",
                '__TYPE' => $this->type,
                '__DETAILS' => $detail
        ));

        if(is_array($this->callback)) {
            call_user_func(array($this->callback['object'], $this->callback['function']), $this->getErrors());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addErrors(array $errors): \codename\core\errorstack
    {
      foreach($errors as $error) {
        array_push($this->errors, $error);
      }
      if(is_array($this->callback)) {
          call_user_func(array($this->callback['object'], $this->callback['function']), $this->getErrors());
      }
      return $this;
    }
    /**
     * @inheritDoc
     */
    public function addErrorstack(\codename\core\errorstack $errorstack): \codename\core\errorstack {
      $this->addErrors($errorstack->getErrors());
      return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\errorstack_interface::isSuccess()
     */
    final public function isSuccess() : bool {
        return (count($this->getErrors()) == 0);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\errorstack_interface::getErrors()
     */
    final public function getErrors() : array {
        return $this->errors;
    }

    /**
     * Adds a callback for errors.
     * <br />Add the $object and the $function of the object that will be called
     * @param object $object
     * @param string $function
     */
    final public function setCallback($object, string $function) {
        $this->callback = array(
                'object' => $object,
                'function' => $function
        );
        return;
    }

    /**
     * @inheritDoc
     */
    public function reset() : \codename\core\errorstack
    {
      $this->errors = array();
      return $this;
    }

    /**
     * @inheritDoc
     * custom serialization
     */
    public function jsonSerialize()
    {
      return $this->getErrors();
    }

}
