<?php
namespace codename\core\tests\request;

/**
 * Test some request functionality
 */
class httpTest extends \codename\core\tests\requestTest {

  /**
   * [testDatacontainer description]
   */
  public function testRequestDatacontainer(): void {
    $request = new \codename\core\request\http([]);
    $this->assertEquals(array_merge($_GET, $_POST, [ 'lang' => 'de_DE']), $request->getData());
  }

  /**
   * [testDatacontainer description]
   */
  public function testHttpsSupport(): void {
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
    $request = new \codename\core\request\http([]);
    $this->assertEquals('on', $_SERVER['HTTPS']);
  }

}
