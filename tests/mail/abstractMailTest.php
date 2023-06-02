<?php

namespace codename\core\tests\mail;

use codename\core\exception;
use codename\core\mail;
use codename\core\tests\base;
use ReflectionException;

abstract class abstractMailTest extends base
{
    /**
     * random failure possible
     * @var string
     */
    public const CHAOSMONKEY_RANDOM = 'random';
    /**
     * you'll receive a disconnect, for sure.
     * @var string
     */
    public const CHAOSMONKEY_DISCONNECT = 'disconnect';
    /**
     * you'll be rejected at any times, even connecting
     * @var string
     */
    public const CHAOSMONKEY_REJECT_CONNECTION = 'reject_connection';
    /**
     * reject MAIL FROM
     * @var string
     */
    public const CHAOSMONKEY_REJECT_MAIL_FROM = 'reject_mail_from';
    /**
     * reject RCPT TO
     * @var string
     */
    public const CHAOSMONKEY_REJECT_RCPT_TO = 'reject_rcpt_to';
    /**
     * reject AUTH
     * @var string
     */
    public const CHAOSMONKEY_REJECT_AUTH = 'reject_auth';

    /**
     * @return void
     */
    protected function testSendMail(): void
    {
        // Make sure mailsink is empty before running this test
        static::assertEmpty($this->tryFetchMail());

        $mail = $this->getMail();
        $success = $mail
          ->setFrom('me@mail.com', 'Sender Name')
          ->addTo('recipient@mail.com', 'Recipient Name')
          ->setSubject('Test email 1')
          ->setBody('Hello!')
          ->send();
        static::assertTrue($success);

        static::assertNotEmpty($this->tryFetchMail(['Subject' => 'Test email 1']));
    }

    /**
     * @param array|null $params
     * @return array|null
     */
    abstract protected function tryFetchMail(?array $params = null): ?array;

    /**
     * @param array|null $config
     * @return mail
     */
    abstract public function getMail(?array $config = null): mail;

    /**
     * @return void
     */
    protected function testRejectConnection(): void
    {
        $this->setChaosMonkey(true, [static::CHAOSMONKEY_REJECT_CONNECTION]);

        $mail = $this->getMail();
        $mail
          ->setFrom('me@mail.com', 'Sender Name')
          ->addTo('recipient@mail.com', 'Recipient Name')
          ->setSubject('Test email connection rejected')
          ->setBody('Hello!')
          ->send();
    }

    /**
     * @param bool $state
     * @param array $options
     * @return void
     */
    abstract protected function setChaosMonkey(bool $state, array $options = []): void;

    /**
     * @return void
     */
    protected function testDisconnect(): void
    {
        $this->setChaosMonkey(true, [static::CHAOSMONKEY_DISCONNECT]);

        $mail = $this->getMail();
        $mail
          ->setFrom('me@mail.com', 'Sender Name')
          ->addTo('recipient@mail.com', 'Recipient Name')
          ->setSubject('Test email disconnect')
          ->setBody('Hello!')
          ->send();
    }

    /**
     * @return void
     */
    protected function testRejectSender(): void
    {
        $this->setChaosMonkey(true, [static::CHAOSMONKEY_REJECT_MAIL_FROM]);

        $mail = $this->getMail();
        $mail
          ->setFrom('me@mail.com', 'Sender Name')
          ->addTo('recipient@mail.com', 'Recipient Name')
          ->setSubject('Test email reject sender')
          ->setBody('Hello!')
          ->send();
    }

    /**
     * @return void
     */
    protected function testRejectRecipient(): void
    {
        $this->setChaosMonkey(true, [static::CHAOSMONKEY_REJECT_RCPT_TO]);

        $mail = $this->getMail();
        $mail
          ->setFrom('me@mail.com', 'Sender Name')
          ->addTo('recipient@mail.com', 'Recipient Name')
          ->setSubject('Test email reject recipient')
          ->setBody('Hello!')
          ->send();
    }

    /**
     * @return void
     */
    protected function testRejectAuth(): void
    {
        $this->setChaosMonkey(true, [static::CHAOSMONKEY_REJECT_AUTH]);

        $mail = $this->getMail();
        $mail
          ->setFrom('me@mail.com', 'Sender Name')
          ->addTo('recipient@mail.com', 'Recipient Name')
          ->setSubject('Test email reject auth')
          ->setBody('Hello!')
          ->send();
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function setUp(): void
    {
        $app = static::createApp();
        $app::getAppstack();

        static::setEnvironmentConfig([
          'test' => [
            'filesystem' => [
              'local' => [
                'driver' => 'local',
              ],
            ],
            'log' => [
              'errormessage' => [
                'driver' => 'system',
                'data' => [
                  'name' => 'dummy',
                ],
              ],
              'debug' => [
                'driver' => 'system',
                'data' => [
                  'name' => 'dummy',
                ],
              ],
            ],
          ],
        ]);

        $this->setChaosMonkey(false);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        // delete all mails created in the test
        $this->deleteMail();
        parent::tearDown();
    }

    /**
     * @param array|null $params
     * @return bool
     */
    abstract protected function deleteMail(?array $params = null): bool;
}
