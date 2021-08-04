<?php
namespace codename\core\tests\bucket;

use codename\core\tests\bucket\abstractBucketTest;

class sftpTest extends abstractBucketTest {

  /**
   * @inheritDoc
   */
  public function getBucket(?array $config = null): \codename\core\bucket
  {
    // print_r([ 'getBucket' => $config ]);
    if($config === null) {
      //
      // Default test bucket
      //
      $config = [
        // Default config
        'basedir' => '/share/',
        // 'sftp_method' => \codename\core\bucket\sftp::METHOD_SFTP,
        'sftpserver' => [
          'host' => 'unittest-sftp',
          'port' => 22,
          'auth_type' => 'password',
          'user' => 'unittest-sftp-user-auth-pw',
          'pass' => 'unittest-sftp-user-pass'
        ]
        // 'public'  => false,
      ];
    }

    $hash = md5(serialize($config));

    if(!(static::$instances[$hash] ?? false)) {
      static::$instances[$hash] = new \codename\core\bucket\sftp($config);
    }
    return static::$instances[$hash];

  }

  protected static $instances = [];

  /**
   * @inheritDoc
   */
  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();

    // wait for rmysql to come up
    if(!\codename\core\tests\helper::waitForIt('unittest-sftp', 22, 3, 3, 5)) {
      throw new \Exception('Failed to connect to sftp server');
    }
  }

  /**
   * [testUnreachableHost description]
   */
  public function testUnreachableHost(): void {
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage('EXCEPTION_BUCKET_SFTP_SSH_CONNECTION_FAILED');
    $this->getBucket( [
      // Default config
      'basedir' => '/share/',
      'sftpserver' => [
        'host' => 'nonexisting-sftp',
        'port' => 22,
        'auth_type' => 'password',
        'user' => 'unittest-sftp-user-auth-pw',
        'pass' => 'unittest-sftp-user-pass'
      ]
    ]);
  }

}
