<?php
namespace codename\core\request;

/**
 * access $_FILES or other fileupload-keys
 * in a request
 */
interface filesInterface {
  /**
   * [getFiles description]
   * @return array [description]
   */
  function getFiles() : array;
}
