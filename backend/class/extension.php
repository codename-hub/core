<?php
namespace codename\core;

/**
 * extension base class
 */
abstract class extension extends \codename\core\bootstrap {

  /**
   * [getExtensionName description]
   * @return string [description]
   */
  public abstract function getExtensionName() : string;

  /**
   * [getExtensionVendor description]
   * @return string [description]
   */
  public abstract function getExtensionVendor() : string;

  /**
   * Returns parameters used for injecting the extension
   * into an appstack
   *
   * @return array [injection parameters]
   */
   final public function getInjectParameters() : array {
    return [
      'vendor'    => $this->getExtensionVendor(),
      'app'       => $this->getExtensionName(),
      'namespace' => '\\'.(new \ReflectionClass($this))->getNamespaceName()
    ];
  }

}
