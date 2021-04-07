<?php
namespace codename\core\tests\mail;

use codename\core\tests\base;

abstract class abstractMailTest extends base {

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    $app = static::createApp();
    $app->getAppstack();

    static::setEnvironmentConfig([
      'test' => [
        'filesystem' =>[
          'local' => [
            'driver' => 'local',
          ]
        ],
        'log' => [
          'errormessage' => [
            'driver' => 'system',
            'data' => [
              'name' => 'dummy'
            ]
          ],
          'debug' => [
            'driver' => 'system',
            'data' => [
              'name' => 'dummy'
            ]
          ]
        ],
      ]
    ]);
  }

  /**
   * [getMail description]
   * @param  array|null $config
   * @return \codename\core\mail [description]
   */
  public abstract function getMail(?array $config = null): \codename\core\mail;

  /**
   * @inheritDoc
   */
  protected function tearDown(): void
  {
    // delete all mails created in the test
    $this->deleteMail();
    parent::tearDown();
  }

  /**
   * [testSendMail description]
   */
  public function testSendMail(): void {
    // Make sure mailsink is empty before running this test
    $this->assertEmpty($this->tryFetchMail());

    $mail = $this->getMail();
    $success = $mail
      ->setFrom('me@mail.com', 'Sender Name')
      ->addTo('recipient@mail.com', 'Recipient Name')
      ->setSubject('Test email 1')
      ->setBody('Hello!')
      ->send();
    $this->assertTrue($success);

    $this->assertNotEmpty($this->tryFetchMail(['Subject' => 'Test email 1']));
  }

  /**
   * [tryFetchMail description]
   * @param  array|null   $params [description]
   * @return array|null
   */
  protected abstract function tryFetchMail(?array $params = null): ?array;

  /**
   * [deleteMail description]
   * @param  array|null $params [description]
   * @return bool           [description]
   */
  protected abstract function deleteMail(?array $params = null): bool;

}
