<?php

namespace codename\core\tests\mail;

use codename\core\mail;
use codename\core\mail\PHPMailer;
use codename\core\tests\helper;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class PHPMailerTest extends abstractMailTest
{
    /**
     * @return void
     */
    public function testSendMail(): void
    {
        parent::testSendMail();
    }

    /**
     * @return void
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Preliminary check, if DNS is not available
        // we simply assume there's no host for testing, skip.
        if (!gethostbynamel('unittest-smtp')) {
            static::markTestSkipped('SMTP server unavailable, skipping.');
        }

        // wait for smtp to come up
        if (!helper::waitForIt('unittest-smtp', 1025, 3, 3, 5)) {
            throw new Exception('Failed to connect to smtp server');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getMail(?array $config = null): mail
    {
        if ($config === null) {
            //
            // default config
            //
            $config = [
              'host' => 'unittest-smtp',
              'port' => 1025,
              'user' => 'someuser',
              'pass' => 'somepass',
              'secure' => null,
              'auth' => true, // ??
            ];
        }

        return new PHPMailer($config);
    }

    /**
     * {@inheritDoc}
     */
    public function testRejectConnection(): void
    {
        $this->expectException(\PHPMailer\PHPMailer\Exception::class);
        $this->expectExceptionMessageMatches('/SMTP Error: Could not connect to SMTP hos.|SMTP connect\(\) failed\./');
        parent::testRejectConnection();
    }

    /**
     * {@inheritDoc}
     */
    public function testRejectAuth(): void
    {
        $this->expectException(\PHPMailer\PHPMailer\Exception::class);
        $this->expectExceptionMessageMatches('/Could not authenticate./');
        parent::testRejectAuth();
    }

    /**
     * {@inheritDoc}
     */
    public function testRejectSender(): void
    {
        $this->expectException(\PHPMailer\PHPMailer\Exception::class);
        $this->expectExceptionMessageMatches('/MAIL FROM command failed/');
        parent::testRejectSender();
    }

    /**
     * {@inheritDoc}
     */
    public function testRejectRecipient(): void
    {
        $this->expectException(\PHPMailer\PHPMailer\Exception::class);
        $this->expectExceptionMessageMatches('/Invalid recipient/');
        parent::testRejectRecipient();
    }

    /**
     * {@inheritDoc}
     */
    public function testDisconnect(): void
    {
        $this->expectException(\PHPMailer\PHPMailer\Exception::class);
        // We can't know when this is really killing it. ?
        // $this->expectExceptionMessageMatches('/SMTP connect\(\) failed\./'); // ?
        parent::testDisconnect();
    }

    /**
     * {@inheritDoc}
     * @param bool $state
     * @param array $options
     * @throws GuzzleException
     */
    protected function setChaosMonkey(bool $state, array $options = []): void
    {
        $client = $this->getMailhogV2Client();
        $currentState = null;

        // get current state
        try {
            $client->get('jim');
            $currentState = true;
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                // jim not enabled (yet)
                $currentState = false;
            }
        }

        if ($state) {
            //
            // TODO: random == full randomization? or just some defaults?
            //
            $params = [
              'AcceptChance' => in_array(static::CHAOSMONKEY_REJECT_CONNECTION, $options) ? 0.0 : 1.0,
              'DisconnectChance' => in_array(static::CHAOSMONKEY_DISCONNECT, $options) ? 1.0 : 0.0,
              'RejectSenderChance' => in_array(static::CHAOSMONKEY_REJECT_MAIL_FROM, $options) ? 1.0 : 0.0,
              'RejectRecipientChance' => in_array(static::CHAOSMONKEY_REJECT_RCPT_TO, $options) ? 1.0 : 0.0,
              'RejectAuthChance' => in_array(static::CHAOSMONKEY_REJECT_AUTH, $options) ? 1.0 : 0.0,
            ];

            if (!$currentState) {
                // enable jim
                $client->post('jim', [
                  RequestOptions::JSON => $params,
                ]);
            } else {
                // enable jim
                $client->put('jim', [
                  RequestOptions::JSON => $params,
                ]);
            }

            $client->get('jim');
        } else {
            // disable jim
            if ($currentState) {
                $client->delete('jim');
            }

            try {
                $client->get('jim');
            } catch (Exception) {
            }
        }
    }

    /**
     * [getMailhogV2Client description]
     * @param array|null $config [description]
     * @return Client             [description]
     */
    protected function getMailhogV2Client(?array $config = null): Client
    {
        return new Client([
          'base_uri' => 'http://unittest-smtp:8025/api/v2/',
        ]);
    }

    /**
     * {@inheritDoc}
     * @param array|null $params
     * @return bool
     * @throws GuzzleException
     */
    protected function deleteMail(?array $params = null): bool
    {
        if (!$params) {
            // delete all mails
            $this->getMailhogClient()->delete('messages');
        } else {
            // TODO: delete only specific?
        }
        return true;
    }

    /**
     * @param array|null $config
     * @return Client
     */
    protected function getMailhogClient(?array $config = null): Client
    {
        return new Client([
          'base_uri' => 'http://unittest-smtp:8025/api/v1/',
        ]);
    }

    /**
     * {@inheritDoc}
     * @param array|null $params
     * @return array|null
     * @throws GuzzleException
     */
    protected function tryFetchMail(?array $params = null): ?array
    {
        $client = $this->getMailhogClient();
        $response = $client->get('messages');

        $result = json_decode($response->getBody()->getContents(), true);

        // comment-in for DEBUG:
        // print_r($result);

        if ($params) {
            // TODO: search for a specific mail?
            $result = array_values(
                array_filter($result, function ($entry) use ($params) {
                    if ($params['Subject'] ?? false) {
                        if ($entry['Content']['Headers']['Subject'][0] != $params['Subject']) {
                            return false;
                        }
                    }

                    return true;
                })
            );
        }

        return array_map(function ($entry) {
            return $entry['Content'];
        }, $result);
    }
}
