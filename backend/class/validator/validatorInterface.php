<?php
namespace codename\core\validator;

/**
 * Definition for \codename\core\validator
 * @package core
 * @since 2016-02-04
 */
interface validatorInterface {

    /**
     * Sends the $value to the instance and performs validation by calling the validateValue function. Returns the array of erros.
     * @param multitype $value
     * @return array
     */
    public function validate($value) : array;

    /**
     * Sends the $value to the validate function and returns true, if the array of errors is empty.
     * @param multitype $value
     * @return boolean
     */
    public function isValid($value) : bool;

    /**
     * Returns all the errors that exist in the instance.
     * @return array
     */
    public function getErrors() : array;

    /**
     * reset the errorstack inside the validator
     * @return \codename\core\validator
     */
    public function reset() : \codename\core\validator;

}
