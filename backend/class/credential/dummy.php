<?php
namespace codename\core\credential;

/**
 * Dummy credential object
 */
class dummy extends \codename\core\credential {


  /**
   * @inheritDoc
   */
  public function getIdentifier() : string
  {
    return null;
  }

  /**
   * @inheritDoc
   */
  public function getAuthentication() : string
  {
    return null;
  }
}
