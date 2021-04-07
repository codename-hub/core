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
        'user'    => null,
        'pass'    => null,
        'secure'  => null,
        'auth'    => null, // ??
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

}
