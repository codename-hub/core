<?php
namespace codename\core\bucketdipper;
use \codename\core\accesscontrol;
use \codename\core\accesscontrol\policy;

use \codename\core\app;
use codename\core\exception;

/**
 * Create a signed url (link) to a file in a bucket
 * @package core
 * @author Kevin Dargel
 * @since 2016-11-02
 */
class urlsignature extends \codename\core\accesscontrol\policysignature {

  /**
   * publicly accessible ID.
   * To be set after creating a new db entry, if needed.
   * NOT DONE IN THIS CLASS!
   * @var integer
   */
  public $id = 0;

  /**
   * @var \codename\core\bucket\bucketInterface
   */
  protected $bucket = NULL;

  /**
   * @var \codename\core\value\text\filerelative
   */
  protected $file = NULL;

  /**
   *
   */
  public function __construct(\codename\core\bucket\bucketInterface $bucket, \codename\core\value\text\filerelative $file, \codename\core\accesscontrol\policy\policyInterface $policy = NULL)
  {
    $this->bucket = $bucket;
    $this->file = $file;

    $this->parameters['file'] = $this->file->get();

    if($policy !== NULL) {
      $this->policies[] = $policy;
    }
  }

  protected function getHashComponents($policyValues) {
    $components = parent::getHashComponents($policyValues);
    $components[] = $this->file->get();
    return $components;
  }

}
