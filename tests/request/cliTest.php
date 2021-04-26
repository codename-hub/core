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
    $this->addWarning('optimize check');

    $request = new \codename\core\request\cli();
    $this->assertEquals(array_merge([ 'lang' => 'de_DE']), $request->getData());
  }

}
