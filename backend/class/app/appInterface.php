<?php

namespace codename\core\app;

use codename\core\value\text\actionname;
use codename\core\value\text\contextname;
use codename\core\value\text\viewname;

/**
 * Class interface definition for \codename\core\app classes.
 * @package core
 * @since 2016-04-05
 */
interface appInterface
{
    /**
     * Returns the array of data stored in the app configuration identified by its $type and the $key.
     * APPJSON[$type][$key]
     * @param string $type
     * @param string $key
     * @access public
     */
    public static function getData(string $type, string $key);

    /**
     * Return true if the given $context exists in the current app.
     * @param contextname $context
     * @return bool
     * @access public
     */
    public function contextExists(contextname $context): bool;

    /**
     * Return true if the $view exists in the $context of the current app.
     * @param contextname $context
     * @param viewname $view
     * @return bool
     * @access public
     */
    public function viewExists(contextname $context, viewname $view): bool;

    /**
     * Return true if the $action exists in the $context of the current app.
     * @param contextname $context
     * @param actionname $action
     * @return bool
     * @access public
     */
    public function actionExists(contextname $context, actionname $action): bool;

    /**
     * Runs the application after it has been initialized. It deals with the action, view-function, the view output and the template by itself.
     * @return null
     * @access public
     */
    public function run();
}
