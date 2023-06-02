<?php

namespace codename\core;

use codename\core\value\valueInterface;
use ReflectionException;

/**
 * This is a future implementation of the data object
 * @package core
 * @since 2016-08-10
 */
class value implements valueInterface
{
    /**
     * I cannot instance, because the given $value cannot be validated against my validator.
     * @var string
     */
    public const EXCEPTION_CONSTRUCT_INVALIDDATATYPE = 'EXCEPTION_CONSTRUCT_INVALIDDATATYPE';

    /**
     * I contain the precise value
     * @var mixed|null
     */
    protected mixed $value = null;

    /**
     * This validator is used to validate the value on generation.
     * @var string
     */
    protected string $validator = 'text';

    /**
     * I will set in the value
     * @param mixed $value
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(mixed $value)
    {
        if (count($errors = app::getValidator($this->validator)->reset()->validate($value)) > 0) {
            throw new exception(self::EXCEPTION_CONSTRUCT_INVALIDDATATYPE, exception::$ERRORLEVEL_FATAL, $errors);
        }
        $this->value = $value;
        unset($this->validator);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see valueInterface::get
     */
    public function get(): mixed
    {
        return $this->value;
    }
}
