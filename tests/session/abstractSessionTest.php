<?php
namespace codename\core\tests\session;

use codename\core\app;

use codename\core\tests\base;
use codename\core\tests\overrideableApp;

abstract class abstractSessionTest extends base {

  /**
   * should return a database config for 'default' connection
   * @return array
   */
  protected abstract function getDefaultSessionConfig(): array;

  /**
   * [getAdditionalEnvironmentConfig description]
   * @return array [description]
   */
  protected function getAdditionalEnvironmentConfig(): array {
    return [];
  }

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    $app = static::createApp();
    $app->getAppstack();

    static::setEnvironmentConfig([
      'test' => array_merge([
        'session' => [
          'default' => $this->getDefaultSessionConfig()
        ],
        // 'filesystem' =>[
        //   'local' => [
        //     'driver' => 'local',
        //   ]
        // ],
        // 'log' => [
        //   'debug' => [
        //     'driver' => 'system',
        //     'data' => [
        //       'name' => 'dummy'
        //     ]
        //   ]
        // ]
      ],
      $this->getAdditionalEnvironmentConfig()
    )]);
  }

  /**
   * @inheritDoc
   */
  protected function tearDown(): void
  {
    $this->emulateSession(null);
    app::getSession()->destroy();
    parent::tearDown();
  }

  /**
   * Emulates a session (or none, if null)
   * if key 'valid' is not supplied, we automatically assume
   * a valid session to be emulated
   * $data might be an array of one or more of these keys:
   * (string) identifier    (e.g. a cookie value or header)
   * (bool)   valid         (whether the session was recently evaluated as valid)
   * (string) valid_until   (ISO datetime of expiry)
   * @param  array|null $data [description]
   */
  protected function emulateSession(?array $data): void {
    return;
  }

  /**
   * [testUnidentified description]
   */
  public function testUnidentified(): void {
    $this->emulateSession(null);
    $this->assertFalse(app::getSession()->identify());
  }

  /**
   * [testStart description]
   */
  public function testBasicIo(): void {
    $this->emulateSession(null);
    $this->assertFalse(app::getSession()->identify());

    app::getSession()->start([
      'session_data' => [
        'dummy' => true
      ],
      'dummy' => true,
    ]);

    $this->assertTrue(app::getSession()->identify());
    $this->assertTrue(app::getSession()->isDefined('dummy'));
    $this->assertFalse(app::getSession()->isDefined('nonexisting'));

    $this->assertEquals(true, app::getSession()->getData('dummy'));

    app::getSession()->setData('dummy', 'some-value');
    $this->assertEquals('some-value', app::getSession()->getData('dummy'));

    // TODO: Not supported for every driver right now:
    // app::getSession()->unsetData('dummy');
    // $this->assertFalse(app::getSession()->isDefined('dummy'));

    app::getSession()->destroy();

    $this->assertFalse(app::getSession()->identify());
  }

  /**
   * @inheritDoc
   */
  public function testEmulatedSessionIo(): void
  {
    // Emulate a nonexisting session
    $this->emulateSession(null);
    $this->assertFalse(app::getSession()->identify());

    // Emulate an existing session afterwards
    $this->emulateSession([
      'identifier' => 'some-random-session',
    ]);

    // due to cookie limitations on CLI
    // this might throw a WarningException, if not suppressed this way
    @app::getSession()->start([
      'session_data' => [
        'dummy' => true
      ],
      'dummy' => true,
    ]);

    $this->assertTrue(app::getSession()->identify());
    // print_r(app::getSession()->getData());
    $this->assertTrue(app::getSession()->isDefined('dummy'));
    $this->assertFalse(app::getSession()->isDefined('nonexisting'));

    $this->assertEquals(true, app::getSession()->getData('dummy'));

    app::getSession()->setData('dummy', 'some-value');
    $this->assertEquals('some-value', app::getSession()->getData('dummy'));

    // TODO: Not supported for every driver right now:
    // app::getSession()->unsetData('dummy');
    // $this->assertFalse(app::getSession()->isDefined('dummy'));

    // due to cookie limitations on CLI
    // this might throw a WarningException, if not suppressed this way
    @app::getSession()->destroy();

    $this->assertFalse(app::getSession()->identify());

    // Emulate a nonexisting session again
    $this->emulateSession(null);
  }

  /**
   * [testInvalidSessionIdentify description]
   */
  public function testInvalidSessionIdentify(): void {
    // Emulate an existing session
    $this->emulateSession([
      'identifier'  => 'some-valid-session',
      'valid'       => true,
    ]);
    $this->assertTrue(app::getSession()->identify());

    $this->emulateSession([
      'identifier'  => 'some-invalid-session',
      'valid'       => false,
    ]);
    $this->assertFalse(app::getSession()->identify());
  }

  /**
   * [testInvalidSession description]
   */
  public function testExpiredSession(): void {
    // Emulate an existing session
    $this->emulateSession([
      'identifier'  => 'some-expired-session',
      'valid'       => true,
      'valid_until' => (new \DateTime('now'))->modify('- 1 day')->format('Y-m-d H:i:s')
    ]);
    $this->assertFalse(@app::getSession()->identify());
  }

  /**
   * [testInvalidateSession description]
   */
  public function testInvalidateSession(): void {
    // Emulate an existing session
    $this->emulateSession([
      'identifier'  => 'some-valid-session',
      'valid'       => true,
    ]);

    @app::getSession()->start([
      'session_data' => [
        'dummy' => true
      ],
      'dummy' => true,
    ]);

    $this->assertTrue(app::getSession()->identify());
    $this->assertNull(app::getSession()->invalidate('some-valid-session'));
    $this->assertFalse(app::getSession()->identify());
  }

  /**
   * [testInvalidateInvalidSession description]
   */
  public function testInvalidateInvalidSession(): void {
    $this->expectException(\Exception::class);
    app::getSession()->invalidate(null);
  }

}
