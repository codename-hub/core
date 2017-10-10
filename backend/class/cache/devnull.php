<?php
namespace codename\core\cache;

/**
 * Client for devnull cache (dummy)
 * @package core
 */
class devnull extends \codename\core\cache {

  /**
   * @inheritDoc
   */
  public function get(string $group, string $key)
  {
    return null;
  }

  /**
   * @inheritDoc
   */
  public function set(
    string $group,
    string $key,
    $value = null,
    int $timeout = null
  ) {
  }

  /**
   * @inheritDoc
   */
  public function isDefined(string $group, string $key) : bool
  {
    return false;
  }

  /**
   * @inheritDoc
   */
  public function clearKey(string $group, string $key)
  {
  }

  /**
   * @inheritDoc
   */
  public function clear(string $key)
  {
  }

  /**
   * @inheritDoc
   */
  public function clearGroup(string $group)
  {
  }
}