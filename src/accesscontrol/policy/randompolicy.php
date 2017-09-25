<?php
namespace codename\core\accesscontrol\policy;

/**
 * Policy for randomized allowed access. Because fuck you, that's why.
 */
class randompolicy implements policyInterface {
  /**
   * @inheritDoc
   */
  public function getDescriptor(): string
  {
    return "randompolicy";
  }

  /**
   * @inheritDoc
   */
  public function obsoleteIfInvalid(): bool
  {
    return false;
  }

  /**
   * @inheritDoc
   */
  public function validate(array $parameters): bool
  {
    $randomNumber = rand(1,3);
    if($randomNumber == 2) {
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
  public function getValue(array $parameters): string
  {
    return "random";
  }

  /**
   * @inheritDoc
   */
  public function getParameters(): array
  {
    return array();
  }

  /**
   * @inheritDoc
   */
  public function setParameters(array $data)
  {
  }
}
