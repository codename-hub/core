<?php

namespace codename\core;

use codename\core\value\text\actionname;
use codename\core\value\text\contextname;
use codename\core\value\text\viewname;

/**
 * Collecting and accessing data that is sent by the request
 * @package core
 * @since 2016-01-24
 */
class request extends datacontainer
{
    /**
     * Contains the context
     * @var null|contextname
     */
    private ?value\text\contextname $context = null;

    /**
     * contains the view name
     * @var null|viewname
     */
    private ?value\text\viewname $view = null;

    /**
     * Contains the action
     * @var null|actionname
     */
    private ?value\text\actionname $action = null;

    /**
     * Create instance of request, merge _POST and _GET super globals to the instance data
     * @return request
     */
    public function __construct()
    {
        parent::__construct();
        return $this;
    }
}
