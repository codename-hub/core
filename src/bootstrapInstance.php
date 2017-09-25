<?php
namespace codename\core;
use \codename\core\app;

/**
 * We're keeping some static variables and methods out of the old bootstrap class
 * this makes extensibility a mess otherwise.
 * we're bridging static and instance methods this way.
 * @package codename\core
 * @author Kevin Dargel
 * @since 2017-04-19
 */
class bootstrapInstance {

    /**
     * Returns an instance of the requested $model from the given $app or the current app
     * @author Kevin Dargel
     * @param string $model
     * @param string $app
     * @param string $vendor
     * @return model
     */
    public function getModel(string $model = '', string $app = '', string $vendor = '') : model {
        return bootstrap::getModel($model, $app, $vendor);
    }

    /**
     * Returns an instance of the current request container.
     * @author Kevin Dargel
     * @return request
     */
    public function getRequest() : \codename\core\request {
        return bootstrap::getRequest();
    }

    /**
     * Returns an instance of the current response container
     * @author Kevin Dargel
     * @return response
     */
    public function getResponse() : \codename\core\response {
        return bootstrap::getResponse();
    }

}
