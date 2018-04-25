<?php
namespace codename\core\model;

/**
 * defines an interface for handling cross-model
 * virtual nested fields
 */
interface virtualFieldResultInterface {

  /**
   * [getVirtualFieldResult description]
   * @param  array  $result [description]
   * @param  array  $track  [description]
   * @return [type]         [description]
   */
  function getVirtualFieldResult(array $result, &$track = []);

  /**
   * [setVirtualFieldResult description]
   * @param bool $state [description]
   */
  function setVirtualFieldResult(bool $state);
}
