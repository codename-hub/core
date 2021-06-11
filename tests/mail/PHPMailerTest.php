<?php
namespace codename\core\tests\bucket;

use codename\core\tests\mail\abstractMailTest;

class PHPMailerTest extends abstractMailTest {

  /**
   * @inheritDoc
   */
  public function getMail(?array $config = null): \codename\core\mail
  {
    if($config === null) {
      //
      // default config
      //
      $config = [
        'host'    => 'unittest-smtp',
        'port'    => 1025,
        'user'    => 'someuser',
        'pass'    => 'somepass',
        'secure'  => null,
        'auth'    => true, // ??
      ];
    }

    return new \codename\core\mail\PHPMailer($config);
  }

  /**
   * [getMailhogClient description]
   * @param  array|null            $config  [description]
   * @return \GuzzleHttp\Client             [description]
   */
  protected function getMailhogClient(?array $config = null): \GuzzleHttp\Client {
    $client = new \GuzzleHttp\Client([
      'base_uri' => 'http://unittest-smtp:8025/api/v1/',
    ]);
    return $client;
  }

  /**
   * [getMailhogV2Client description]
   * @param  array|null            $config  [description]
   * @return \GuzzleHttp\Client             [description]
   */
  protected function getMailhogV2Client(?array $config = null): \GuzzleHttp\Client {
    $client = new \GuzzleHttp\Client([
      'base_uri' => 'http://unittest-smtp:8025/api/v2/',
    ]);
    return $client;
  }

  /**
   * @inheritDoc
   */
  protected function setChaosMonkey(bool $state, array $options = [])
  {
    $client = $this->getMailhogV2Client();
    $currentState = null;

    // get current state
    try {
      $response = $client->get('jim');
      $currentState = true;
      // echo("Jim  state:");
      // print_r(json_decode($response->getBody()->getContents(), true));
    } catch (\GuzzleHttp\Exception\ClientException $e) {
      if($e->getResponse()->getStatusCode() === 404) {
        // jim not enabled (yet)
        $currentState = false;
      }
    }

    if($state) {

      //
      // TODO: random == full randomization? or just some defaults?
      //
      $params = [
        'AcceptChance'          => in_array(static::CHAOSMONKEY_REJECT_CONNECTION, $options)  ? 0.0 : 1.0,
        'DisconnectChance'      => in_array(static::CHAOSMONKEY_DISCONNECT, $options)         ? 1.0 : 0.0,
        'RejectSenderChance'    => in_array(static::CHAOSMONKEY_REJECT_MAIL_FROM, $options)   ? 1.0 : 0.0,
        'RejectRecipientChance' => in_array(static::CHAOSMONKEY_REJECT_RCPT_TO, $options)     ? 1.0 : 0.0,
        'RejectAuthChance'      => in_array(static::CHAOSMONKEY_REJECT_AUTH, $options)        ? 1.0 : 0.0,
      ];

      // echo("new params: " );
      // print_r($params);
      // $params = array_filter($params);

      if(!$currentState) {
        // enable jim
        $res = $client->post('jim', [
          \GuzzleHttp\RequestOptions::JSON => $params,
        ]);
        // echo("Jim created:");
        // print_r($res->getBody()->getContents());
      } else {
        // enable jim
        $res = $client->put('jim', [
          \GuzzleHttp\RequestOptions::JSON => $params,
        ]);
        // echo("Jim updated:");
        // print_r($res->getBody()->getContents());
      }

      $response = $client->get('jim');
      $currentState = true;
      // echo("Jim NEW state:");
      // print_r(json_decode($response->getBody()->getContents(), true));


    } else {
      // disable jim
      if($currentState) {
        $res = $client->delete('jim');
        // echo("jim removed.");
      }
      // try {
      // } catch (\GuzzleHttp\Exception\ClientException $e) {
      //   if($e->getResponse()->getStatusCode() === 404) {
      //     // successful deletion
      //   }
      // }

      try {
        $response = $client->get('jim');
        // $currentState = true;
        // echo("jim should be inactive but is not");
        // print_r(json_decode($response->getBody()->getContents(), true));
      } catch (\Exception $e) {

      }
      return;
    }
  }

  /**
   * [setUpBeforeClass description]
   */
  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();

    // wait for smtp to come up
    if(!\codename\core\tests\helper::waitForIt('unittest-smtp', 1025, 3, 3, 5)) {
      throw new \Exception('Failed to connect to smtp server');
    }
  }

  /**
   * @inheritDoc
   */
  protected function deleteMail(?array $params = null): bool
  {
    if(!$params) {
      // delete all mails
      $this->getMailhogClient()->delete('messages');
    } else {
      // TODO: delete only specific?
    }
    return true;
  }

  /**
   * @inheritDoc
   */
  protected function tryFetchMail(?array $params = null): ?array
  {
    $client = $this->getMailhogClient();
    $response = $client->get('messages');

    $result = json_decode($response->getBody()->getContents(), true);

    // comment-in for DEBUG:
    // print_r($result);

    if($params) {
      // TODO: search for a specific mail?
      $result = array_values(array_filter($result, function($entry) use ($params) {

        if($v = $params['Subject']) {
          if($entry['Content']['Headers']['Subject'][0] != $params['Subject']) {
            return false;
          }
        }

        return true;
      }));
    }

    return array_map(function($entry) {
      return $entry['Content'];
    }, $result);
  }

  /**
   * @inheritDoc
   */
  public function testRejectConnection(): void
  {
    $this->expectException(\PHPMailer\PHPMailer\Exception::class);
    $this->expectExceptionMessageMatches('/SMTP connect\(\) failed\./');
    parent::testRejectConnection();
  }

  /**
   * @inheritDoc
   */
  public function testRejectAuth(): void
  {
    $this->expectException(\PHPMailer\PHPMailer\Exception::class);
    $this->expectExceptionMessageMatches('/Could not authenticate./');
    parent::testRejectAuth();
  }

  /**
   * @inheritDoc
   */
  public function testRejectSender(): void
  {
    $this->expectException(\PHPMailer\PHPMailer\Exception::class);
    $this->expectExceptionMessageMatches('/MAIL FROM command failed/');
    parent::testRejectSender();
  }

  /**
   * @inheritDoc
   */
  public function testRejectRecipient(): void
  {
    $this->expectException(\PHPMailer\PHPMailer\Exception::class);
    $this->expectExceptionMessageMatches('/Invalid recipient/');
    parent::testRejectRecipient();
  }

  /**
   * @inheritDoc
   */
  public function testDisconnect(): void
  {
    $this->expectException(\PHPMailer\PHPMailer\Exception::class);
    // We can't know when this is really killing it. ?
    // $this->expectExceptionMessageMatches('/SMTP connect\(\) failed\./'); // ?
    parent::testDisconnect();
  }

}
