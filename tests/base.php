<?php
namespace codename\core\tests;

/**
* NOTE: compat class, inherits base from core-test package
 */
abstract class base extends \codename\core\test\base {

  /**
   * @inheritDoc
   */
  public static function tearDownAfterClass(): void
  {
    parent::tearDownAfterClass();
    \codename\core\tests\overrideableApp::reset();
  }

}
