<?php

namespace codename\core;

use codename\core\credential\credentialInterface;
use ReflectionException;

/**
 * The abstract credential class is the main extension point for all credential classes.
 * @package core
 * @since 2018-02-22
 */
abstract class credential extends config implements credentialInterface
{
    /**
     * [EXCEPTION_REST_CREDENTIAL_VALIDATION description]
     * @var string
     */
    public const EXCEPTION_CORE_CREDENTIAL_VALIDATION = 'EXCEPTION_REST_CREDENTIAL_VALIDATION';

    /**
     * validator name to be used for validating input data
     * @var string|null
     */
    protected static $validatorName = null;

    /**
     * {@inheritDoc}
     * @param array $data
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(array $data)
    {
        // if validator is set, validate!
        if (self::$validatorName != null && count($errors = app::getValidator(self::$validatorName)->validate($data)) > 0) {
            throw new exception(self::EXCEPTION_CORE_CREDENTIAL_VALIDATION, exception::$ERRORLEVEL_FATAL, $errors);
        }
        parent::__construct($data);
    }

    /**
     * {@inheritDoc}
     */
    abstract public function getIdentifier(): string;

    /**
     * {@inheritDoc}
     */
    abstract public function getAuthentication(): mixed;

    /**
     * [public description]
     * @return string
     */
    // abstract public function getAuthenticationHash() : string;
}
