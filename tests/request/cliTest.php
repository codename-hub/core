<?php
namespace codename\core\tests\request;

/**
 * Test some request functionality
 */
class cliTest extends \codename\core\tests\requestTest {

  /**
   * [testDatacontainer description]
   */
  public function testRequestDatacontainer(): void {
    $this->markTestIncomplete('CLI Request may contain phpunit/unittest arguments!');

    $request = new \codename\core\request\cli();
    $this->assertEquals(array_merge([ 'lang' => 'de_DE']), $request->getData());
  }

}
