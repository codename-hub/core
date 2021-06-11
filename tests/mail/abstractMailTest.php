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

    $this->setChaosMonkey(false);
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
   * [testRejectConnection description]
   */
  public function testRejectConnection(): void {
    $this->setChaosMonkey(true, [ static::CHAOSMONKEY_REJECT_CONNECTION ]);

    $mail = $this->getMail();
    $success = $mail
      ->setFrom('me@mail.com', 'Sender Name')
      ->addTo('recipient@mail.com', 'Recipient Name')
      ->setSubject('Test email connection rejected')
      ->setBody('Hello!')
      ->send();
  }

  /**
   * [testDisconnect description]
   */
  public function testDisconnect(): void {
    $this->setChaosMonkey(true, [ static::CHAOSMONKEY_DISCONNECT ]);

    $mail = $this->getMail();
    $success = $mail
      ->setFrom('me@mail.com', 'Sender Name')
      ->addTo('recipient@mail.com', 'Recipient Name')
      ->setSubject('Test email disconnect')
      ->setBody('Hello!')
      ->send();
  }

  /**
   * [testRejectSender description]
   */
  public function testRejectSender(): void {
    $this->setChaosMonkey(true, [ static::CHAOSMONKEY_REJECT_MAIL_FROM ]);

    $mail = $this->getMail();
    $success = $mail
      ->setFrom('me@mail.com', 'Sender Name')
      ->addTo('recipient@mail.com', 'Recipient Name')
      ->setSubject('Test email reject sender')
      ->setBody('Hello!')
      ->send();
  }

  /**
   * [testRejectRecipient description]
   */
  public function testRejectRecipient(): void {
    $this->setChaosMonkey(true, [ static::CHAOSMONKEY_REJECT_RCPT_TO ]);

    $mail = $this->getMail();
    $success = $mail
      ->setFrom('me@mail.com', 'Sender Name')
      ->addTo('recipient@mail.com', 'Recipient Name')
      ->setSubject('Test email reject recipient')
      ->setBody('Hello!')
      ->send();
  }

  /**
   * [testRejectAuth description]
   */
  public function testRejectAuth(): void {
    $this->setChaosMonkey(true, [ static::CHAOSMONKEY_REJECT_AUTH ]);

    $mail = $this->getMail();
    $success = $mail
      ->setFrom('me@mail.com', 'Sender Name')
      ->addTo('recipient@mail.com', 'Recipient Name')
      ->setSubject('Test email reject auth')
      ->setBody('Hello!')
      ->send();
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

  /**
   * [setChaosMonkey description]
   * @param bool  $state   [description]
   * @param array $options [description]
   */
  protected abstract function setChaosMonkey(bool $state, array $options = []);

  /**
   * random failure possible
   * @var string
   */
  const CHAOSMONKEY_RANDOM = 'random';

  /**
   * you'll receive a disconnect, for sure.
   * @var string
   */
  const CHAOSMONKEY_DISCONNECT = 'disconnect';

  /**
   * you'll be rejected at any times, even connecting
   * @var string
   */
  const CHAOSMONKEY_REJECT_CONNECTION = 'reject_connection';

  /**
   * rate limiting?
   * @var string
   */
  // const CHAOSMONKEY_RATELIMIT = 'ratelimit';

  /**
   * reject MAIL FROM
   * @var string
   */
  const CHAOSMONKEY_REJECT_MAIL_FROM = 'reject_mail_from';

  /**
   * reject RCPT TO
   * @var string
   */
  const CHAOSMONKEY_REJECT_RCPT_TO = 'reject_rcpt_to';

  /**
   * reject AUTH
   * @var string
   */
  const CHAOSMONKEY_REJECT_AUTH = 'reject_auth';
}
