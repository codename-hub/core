<?php
namespace codename\core\model;

/**
 * defines an interface for handling cross-model
 * virtual nested fields
 */
interface virtualFieldResultInterface {

  /**
   * creates/fills virtual fields with their respective data, on need
   * should only be used internally
   *
   * @param  array  $result [input result]
   * @param  array  $track  [temporary/internal tracking array for nesting level calculations]
   * @return array          [modified result]
   */
  function getVirtualFieldResult(array $result, &$track = []);

  /**
   * changes the state of the virtual field result handling
   *
   * @param bool $state [state of the virtual field result handling]
   * @return \codename\core\model [current instance]
   */
  function setVirtualFieldResult(bool $state) : \codename\core\model;
}
