<?php
namespace codename\core\accesscontrol;
use \codename\core\app;
use codename\core\exception;

/**
 * A collection of policies, resulting in a policy signature.
 * @package core
 * @author Kevin Dargel
 * @since 2016-11-02
 */
class policysignature {

  /**
   * Access Policies
   * @var \codename\core\accesscontrol\policy\policyInterface[]
   */
  protected $policies = array();

  /**
   *
   */
  public function __construct()
  {
  }

  /**
   * Returns a signature value as base64-encoded string
   * @return string [signature]
   */
  public function get() : string {
    $policyValues = $this->getPolicyValues();
    $hash = $this->getHash($policyValues);
    return $hash;
  }

  protected $parameters = array();

  protected function getParameters() {
    return $this->parameters;
  }

  public function setParameter(string $key, mixed $value) {
    $this->parameters[$key] = $value;
  }

  public function setParameters(array $params) {
    $this->parameters = array_merge($this->parameters, $params);
  }

  public function getPolicyValues() : array {
    $signatures = array();
    foreach($this->policies as $policy) {
      $signatures[$policy->getDescriptor()] = $policy->getValue($this->getParameters());
    }
    return $signatures;
  }

  public function getPolicyData() : array {
    $policies = array();
    foreach($this->policies as $policy) {
      $policies[$policy->getDescriptor()] = $policy->getParameters();
    }
    return $policies;
  }

  protected function getHash($policyValues) {
    $toBeHashed = implode(';', $this->getHashComponents($policyValues));
    return hash('sha512', $toBeHashed);
  }

  /**
   * Returns an array of to-be-hashed components
   * Can be customized to return additional hash components (e.g. a salt)
   * @return array [hash components]
   */
  protected function getHashComponents($policyValues) {
    $arrayKeys = implode(',', array_keys($policyValues));
    $arrayValues = implode(',', array_values($policyValues));
    $components = array(
      $arrayKeys,
      $arrayValues
    );
    return $components;
  }

  /**
   * Validates a given signature value
   * @param string $signature [signature as base64-encoded string]
   * @return bool [isValid]
   */
  public function validate(string $hash) : bool {

    $policyValues = $this->getPolicyValues();
    $hashGoal = $this->getHash($policyValues);

    if($hash === $hashGoal) {
      foreach($this->policies as $policy) {
        $policyParameters = $this->getParameters()[$policy->getDescriptor()] ?? array();
        if($policy->validate($policyParameters) !== TRUE) {
          if($policy->obsoleteIfInvalid()) {
            $this->obsolete = true;
          }
          return false;
        } else {
          if($policy->needsUpdate()) {
            $this->policiesUpdated = true;
          }
        }
      }
    } else {
      return false;
    }
    return true;
  }

  /**
   * Internal state, if any of the policies have requested an update
   */
  protected $policiesUpdated = false;

  /**
   * Internal state, if any of the policies lead to an invalidation/obsoletion of the whole policy collection
   */
  protected $obsolete = false;

  /**
   * Update requested by some policy
   * @return bool [update needed]
   */
  public function needsUpdate() : bool {
    return $this->policiesUpdated;
  }

  /**
   * Policy collection has been invalidated/made obsolete. Needs update!
   * @return bool [policy collection is obsolete]
   */
  public function isObsolete() : bool {
    return $this->obsolete;
  }

  /**
   * Adds a policy to the policy chain.
   * Please remember: always use the same order when adding.
   */
  public function addPolicy(\codename\core\accesscontrol\policy\policyInterface $policy) {
    $this->policies[] = $policy;
  }

}
