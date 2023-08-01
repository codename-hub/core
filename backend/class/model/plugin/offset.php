<?php

namespace codename\core\model\plugin;

use codename\core\model\plugin;
use codename\core\model\plugin\offset\offsetInterface;

/**
 * Tell the model to offset the results
 * @package core
 * @since 2016-02-04
 */
class offset extends plugin implements offsetInterface
{
    /**
     * $count of data rows to offset the result
     * @var int $offset
     */
    public int $offset;

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\model_plugin_offset::__construct(integer $offset)
     */
    public function __construct(int $offset)
    {
        $this->offset = $offset;
        return $this;
    }
}
