<?php
namespace codename\core\tests\session;

use codename\core\app;

use codename\core\tests\overrideableApp;

class databaseTest extends abstractSessionTest {
  /**
   * @inheritDoc
   */
  protected function getDefaultSessionConfig(): array
  {
    return [
      'driver' => 'database'
    ];
  }

  /**
   * [protected description]
   * @var bool
   */
  protected static $initialized = false;

  /**
   * @inheritDoc
   */
  public static function tearDownAfterClass(): void
  {
    parent::tearDownAfterClass();
    static::$initialized = false;
  }

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    parent::setUp();

    // avoid re-init
    if(!static::$initialized) {

      static::$initialized = true;

      static::createModel('testschema', 'session', [
        "field" => [
          "session_id",
          "session_created",
          "session_modified",
          "session_valid",
          "session_valid_until",
          "session_data",
          "session_sessionid"
        ],
        "primary" => [
          "session_id"
        ],
        "index" => [
          "session_sessionid",
          "session_created",
          "session_valid",
          [ "session_sessionid", "session_valid" ]
        ],
        "options" => [
          "session_sessionid" => [
            "length" => 128
          ]
        ],
        "datatype" => [
          "session_id" => "number_natural",
          "session_created"=> "text_timestamp",
          "session_modified" => "text_timestamp",
          "session_valid" => "boolean",
          "session_valid_until" => "text_timestamp",
          "session_data"  => "structure",
          "session_sessionid"=> "text",
        ],
        "connection" => "default"
      ]);

      static::architect('sessiontest', 'codename', 'test');
    }

    $sessionModel = $this->getModel('session');
    $sessionClient = new sessionDatabaseOverridden([], $sessionModel);
    overrideableApp::__injectClientInstance('session', 'default', $sessionClient);
  }

  /**
   * @inheritDoc
   */
  protected function getAdditionalEnvironmentConfig(): array
  {
    return [
      'cache' => [
        'default' => [
          'driver' => 'memory',
        ]
      ],
      'database' => [
        'default' => [
          'driver' => 'sqlite',
          'database_file' => ':memory:',
        ]
      ],
      'filesystem' =>[
        'local' => [
          'driver' => 'local',
        ]
      ],
    ];
  }

  /**
   * [testClassInstance description]
   */
  public function testClassInstance(): void {
    $this->assertInstanceOf(\codename\core\session\database::class, app::getSession());
  }

  /**
   * @inheritDoc
   */
  public function testBasicIo(): void
  {
    $this->markTestSkipped('Generic BasicIo test for database-session not applicable due to cookies');
  }

  /**
   * @inheritDoc
   */
  protected function emulateSession(?array $data): void
  {
    if($data) {
      $cookieValue = $data['identifier'];
      $_COOKIE['core-session'] = $cookieValue;
      $this->getModel('session')->save([
        'session_sessionid'   => $cookieValue,
        'session_valid'       => $data['valid'] ?? true,
        'session_valid_until' => $data['valid_until'] ?? null
      ]);
    } else {
      unset($_COOKIE['core-session']);
    }
  }

  // /**
  //  * @inheritDoc
  //  */
  // public function testBasicDatabaseSessionIo(): void
  // {
  //   $this->emulateSession(null);
  //   $this->assertFalse(app::getSession()->identify());
  //
  //   $this->emulateSession([
  //     'cookie' => 'some-random-session',
  //   ]);
  //
  //   // // Emulate an existing cookie
  //   // $sessionSessionId = 'testcookie';
  //   // $_COOKIE['core-session'] = $sessionSessionId;
  //   //
  //   // $this->getModel('session')->save([
  //   //   'session_sessionid' => $sessionSessionId,
  //   //   'session_valid' => true,
  //   // ]);
  //
  //   // due to cookie limitations on CLI
  //   // this might throw a WarningException, if not suppressed this way
  //   @app::getSession()->start([
  //     'session_data' => [
  //       'dummy' => true
  //     ],
  //     'dummy' => true,
  //   ]);
  //
  //   $this->assertTrue(app::getSession()->identify());
  //   // print_r(app::getSession()->getData());
  //   $this->assertTrue(app::getSession()->isDefined('dummy'));
  //   $this->assertFalse(app::getSession()->isDefined('nonexisting'));
  //
  //   $this->assertEquals(true, app::getSession()->getData('dummy'));
  //
  //   app::getSession()->setData('dummy', 'some-value');
  //   $this->assertEquals('some-value', app::getSession()->getData('dummy'));
  //
  //   // TODO: Not supported for every driver right now:
  //   // app::getSession()->unsetData('dummy');
  //   // $this->assertFalse(app::getSession()->isDefined('dummy'));
  //
  //   // due to cookie limitations on CLI
  //   // this might throw a WarningException, if not suppressed this way
  //   @app::getSession()->destroy();
  //
  //   $this->assertFalse(app::getSession()->identify());
  //
  //   $this->emulateSession(null);
  // }


}

class sessionDatabaseOverridden extends \codename\core\session\database {

  /**
   * @inheritDoc
   */
  public function __construct(array $data = array(), \codename\core\model $sessionModelInstance)
  {
    // $this->staticSessionModel = $sessionModelInstance;
    // parent::__construct($data);
    $this->sessionModel = $sessionModelInstance;
  }
  // /**
  //  * [protected description]
  //  * @var \codename\core\model
  //  */
  // protected $staticSessionModel = null;
  //
  // /**
  //  * @inheritDoc
  //  */
  // protected function internalGetModel(string $model): \codename\core\model
  // {
  //   if($model == 'session') {
  //     return $this->staticSessionModel;
  //   } else {
  //     throw new \Exception('Unsupported');
  //   }
  // }
}
