<?php
namespace codename\core\model\plugin\join;

/**
 * [interface description]
 * @var [type]
 */
interface dynamicJoinInterface
{
  /**
   * [performDynamicJoin description]
   * @param  array $result [description]
   * @return array         [description]
   */
  function dynamicJoin(array $result) : array;
}
