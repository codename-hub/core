<?php

namespace codename\core\validator;

use codename\core\validator;

/**
 * Definition for \codename\core\validator
 * @package core
 * @since 2016-02-04
 */
interface validatorInterface
{
    /**
     * Sends the $value to the instance and performs validation by calling the validateValue function. Returns the array of errors.
     * @param mixed|null $value
     * @return array
     */
    public function validate(mixed $value): array;

    /**
     * Sends the $value to the validate function and returns true, if the array of errors is empty.
     * @param mixed|null $value
     * @return bool
     */
    public function isValid(mixed $value): bool;

    /**
     * Returns all the errors that exist in the instance.
     * @return array
     */
    public function getErrors(): array;

    /**
     * reset the errorstack inside the validator
     * @return validator
     */
    public function reset(): validator;
}
