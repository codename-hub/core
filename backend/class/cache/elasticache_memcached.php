<?php

namespace codename\core\cache;

/**
 * Client for handling elasticache Memcached
 * featuring Cluster Endpoint Discovery
 */
class elasticache_memcached extends memcached
{
    /**
     * {@inheritDoc}
     */
    protected function setOptions(): void
    {
        parent::setOptions();
        $this->memcached->setOption(\Memcached::OPT_CLIENT_MODE, \Memcached::DYNAMIC_CLIENT_MODE);
    }
}
