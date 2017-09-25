<?php
namespace codename\core\accesscontrol\policy;

/**
 * Policy for limiting accesses (count)
 */
class limitedaccess implements policyInterface {

  protected $maximumAccessCount = 0;

  protected $currentAccessCount = 0;

  /**
   * @param integer $expiry [expiry timestamp]
   */
  public function __construct(int $maximumAccessCount = 0)
  {
    $this->maximumAccessCount = $maximumAccessCount;
  }

  /**
   * @inheritDoc
   */
  public function setParameters(array $data)
  {
    $this->maximumAccessCount = $data['maximumAccessCount'] ?? $this->maximumAccessCount;
    $this->currentAccessCount = $data['currentAccessCount'] ?? $this->currentAccessCount;
  }

  /**
   * @inheritDoc
   */
  public function getParameters(): array
  {
    return array(
      'maximumAccessCount' => $this->maximumAccessCount,
      'currentAccessCount' => $this->currentAccessCount
    );
  }

  /**
   * @inheritDoc
   */
  public function getDescriptor(): string
  {
    return "limitedaccess";
  }

  /**
   * @inheritDoc
   */
  public function getValue(array $parameters): string
  {
    // Only return static values, as the hash may change otherwise.
    return ''.$this->maximumAccessCount.'';
  }

  /**
   * @inheritDoc
   */
  public function validate(array $parameters): bool
  {
    if($this->currentAccessCount < $this->maximumAccessCount) {
      $this->currentAccessCount++;
      $this->valueUpdated = true;
      return true;
    } else {
      return false;
    }
  }

  protected $valueUpdated = false;

  /**
   * @inheritDoc
   */
  public function needsUpdate(): bool
  {
    return $this->valueUpdated;
  }

  /**
   * @inheritDoc
   */
  public function obsoleteIfInvalid(): bool
  {
    return true;
  }
}
