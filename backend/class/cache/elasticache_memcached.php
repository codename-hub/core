<?php
namespace codename\core\cache;

/**
 * Client for handling ElastiCache Memcached
 * featuring Cluster Endpoint Discovery
 */
class elasticache_memcached extends \codename\core\cache\memcached {

  /**
   * @inheritDoc
   */
  protected function setOptions()
  {
    parent::setOptions();
    $this->memcached->setOption(\Memcached::OPT_CLIENT_MODE, \Memcached::DYNAMIC_CLIENT_MODE);
  }

}
