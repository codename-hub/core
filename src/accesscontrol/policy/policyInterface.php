<?php
namespace codename\core\accesscontrol\policy;

use \codename\core\app;

/**
 * Provides an interface for a policy - to sign and validate signatures of signedUrls
 * @package core
 * @author Kevin Dargel
 * @since 2016-11-02
 */
interface policyInterface {

  /**
   * Provides a specific name for the policy
   * @author Kevin Dargel
   * @param array $data [associative array of data]
   * @access public
   */
  public function setParameters(array $data);

  /**
   * Provides a specific name for the policy
   * @author Kevin Dargel
   * @return array [returns an array of parameters]
   * @access public
   */
  public function getParameters() : array;

  /**
   * Provides a specific name for the policy
   * @author Kevin Dargel
   * @return string
   * @access public
   */
  public function getDescriptor() : string;

  /**
   * Gets the policy value for hashing
   * Only return static (unchanging) values, as the resulting hash may change otherwise.
   * @author Kevin Dargel
   * @return string
   * @access public
   */
  public function getValue(array $parameters) : string;

  /**
   * Validates a policy value
   * @author Kevin Dargel
   * @param array $parameters
   * @return bool
   * @access public
   */
  public function validate(array $parameters) : bool;

  /**
   * Tells the caller if the validation process is querying an update (model->save)
   * @return bool [policy data update needed]
   */
  public function needsUpdate() : bool;

  /**
   * Tells the caller to invalidate the whole policy collection (is obsolete)
   * @return bool [all policies are obsolete now]
   */
  public function obsoleteIfInvalid() : bool;
}
