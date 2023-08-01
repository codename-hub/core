<?php

namespace codename\core;

use ReflectionException;

/**
 * We're keeping some static variables and methods out of the old bootstrap class
 * this makes extensibility a mess otherwise.
 * we're bridging static and instance methods this way.
 * @package codename\core
 * @since 2017-04-19
 */
class bootstrapInstance
{
    /**
     * Returns an instance of the requested $model from the given $app or the current app
     * @param string $model
     * @param string $app
     * @param string $vendor
     * @return model
     * @throws ReflectionException
     * @throws exception
     */
    public function getModel(string $model, string $app = '', string $vendor = ''): model
    {
        return bootstrap::getModel($model, $app, $vendor);
    }

    /**
     * Returns an instance of the current request container.
     * @return request
     */
    public function getRequest(): request
    {
        return bootstrap::getRequest();
    }

    /**
     * Returns an instance of the current response container
     * @return response
     * @throws exception
     */
    public function getResponse(): response
    {
        return bootstrap::getResponse();
    }
}
