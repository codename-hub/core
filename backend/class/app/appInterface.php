<?php
namespace codename\core\app;

/**
 * Class interface definition for \codename\core\app classes.
 * @package core
 * @since 2016-04-05
 */
interface appInterface {

    /**
     * Return true if the given $context exists in the current app.
     * @param string $context
     * @return bool
     * @access public
     */
    public function contextExists(\codename\core\value\text\contextname $context) : bool;

    /**
     * Return true if the $view exists in the $context of the current app.
     * @param string $context
     * @param string $view
     * @return bool
     * @access public
     */
    public function viewExists(\codename\core\value\text\contextname $context, \codename\core\value\text\viewname $view) : bool;

    /**
     * Return true if the $action exists in the $context of the current app.
     * @param string $context
     * @param string $action
     * @return bool
     * @access public
     */
    public function actionExists(\codename\core\value\text\contextname $context, \codename\core\value\text\actionname $action) : bool;

    /**
     * Runs the application after it has been initialized. It deals with the action, view-function, the view output and the template by itself.
     * @return null
     * @access public
     */
    public function run();

    /**
     * Returns the array of data stored in the app configuration identified by it's $type and the $key.
     * APPJSON[$type][$key]
     * @param string $type
     * @param string $key
     * @access public
     */
    public static function getData(string $type, string $key);

}
