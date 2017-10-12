<?php
namespace codename\core;

/**
 * Collecting and accessing data that is sent by the request
 * @package core
 * @since 2016-01-24
 */
class request extends \codename\core\datacontainer {

    /**
     * Contains the context
     * @var \codename\core\value\text\contextname
     */
    private $context = null;

    /**
     * contains the view name
     * @var \codename\core\value\text\viewname
     */
    private $view = null;

    /**
     * Contains the action
     * @var \codename\core\value\text\actionname
     */
    private $action = null;

    /**
     * Create instance of request, merge _POST and _GET superglobals to the instane data
     * @return \codename\core\request
     */
    public function __construct() {
        parent::__construct();
        return $this;
    }

}
