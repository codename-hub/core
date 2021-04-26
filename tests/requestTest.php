<?php
namespace codename\core\tests;

/**
 * Test some request functionality
 */
class requestTest extends base {

  /**
   * [testDatacontainer description]
   */
  public function testRequestDatacontainer(): void {
    $request = new \codename\core\request();

    $this->assertEquals([], $request->getData());
  }
}
