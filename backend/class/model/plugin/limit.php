<?php

namespace codename\core\model\plugin;

use codename\core\model\plugin;
use codename\core\model\plugin\limit\limitInterface;

/**
 * Tell the model to limit the results
 * @package core
 * @since 2016-02-04
 */
class limit extends plugin implements limitInterface
{
    /**
     * $limit of data rows to write to the result
     * @var int $limit
     */
    public int $limit;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_limit::__construct(integer $offset)
     */
    public function __construct(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }
}
