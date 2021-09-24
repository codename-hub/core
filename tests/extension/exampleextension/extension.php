<?php
namespace codename\core\tests\extension\exampleextension;

class extension extends \codename\core\extension {

  /**
   * @inheritDoc
   */
  public function getExtensionName(): string
  {
    return 'exampleextension';
  }

  /**
   * @inheritDoc
   */
  public function getExtensionVendor(): string
  {
    return 'codename';
  }

}
