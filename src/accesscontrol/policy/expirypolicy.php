<?php
namespace codename\core\accesscontrol\policy;

/**
 * Policy for limiting access via an end-timestamp after which the policy invalidates
 */
class expirypolicy implements policyInterface {

  /**
   * Expiry as unix timestamp
   * @var integer
   */
  protected $expiry = 0;

  /**
   * @param integer $expiry [expiry timestamp]
   */
  public function __construct(int $expiry = 0)
  {
    $this->expiry = $expiry;
  }

  /**
   * @inheritDoc
   */
  public function setParameters(array $data)
  {
    $this->expiry = $data['expiry'];
  }

  /**
   * @inheritDoc
   */
  public function getParameters(): array
  {
    return array(
      'expiry' => $this->expiry
    );
  }

  /**
   * @inheritDoc
   */
  public function getDescriptor(): string
  {
    return "expirypolicy";
  }

  /**
   * @inheritDoc
   */
  public function getValue(array $parameters): string
  {
    return ''.$this->expiry.'';
  }

  /**
   * @inheritDoc
   */
  public function validate(array $parameters): bool
  {
    if(time() < $this->expiry) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * @inheritDoc
   */
  public function needsUpdate(): bool
  {
    return false;
  }

  /**
   * @inheritDoc
   */
  public function obsoleteIfInvalid(): bool
  {
    return true;
  }
}
