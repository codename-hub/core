<?php
namespace codename\core;

class variableConfig extends \codename\core\config {
/**
 * @inheritDoc
 */
public function __construct(array $data)
{
  parent::__construct($data);

  // TODO
}

/**
 * @inheritDoc
 */
public function exists(string $key): bool
{
  return parent::exists($key);

  // TODO
}

/**
 * @inheritDoc
 */
public function get(string $key = '', $default = null)
{
  return parent::get($key, $default);

  // TODO
}

public function getPrefixed() : array {
  $vars = array();
  foreach($this->get() as $k=>$v) {
    $vars[] = '{'.'current:'.$k.'}';
  }
  return $vars;
}

public function set(string $key = '', $value) {
  if(strlen($key) == 0) {
      return;
  }
  $this->data[$key] = $value;
  return;
}

}
