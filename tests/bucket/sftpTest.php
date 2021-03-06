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

    // Preliminary check, if DNS is not available
    // we simply assume there's no host for testing, skip.
    if(!gethostbynamel('unittest-sftp')) {
      static::markTestSkipped('SFTP server unavailable, skipping.');
      return;
    }

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
    $this->expectExceptionMessageMatches('/EXCEPTION_BUCKET_SFTP_SSH_CONNECTION_FAILED|ssh2_connect\(\)\: php_network_getaddresses: getaddrinfo failed: (?:Name or service not known|Temporary failure in name resolution)/');

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

  /**
   * @inheritDoc
   */
  public function testVfsLocalDirNotWritableFilePull(): void
  {
    // SFTP bucket throws a custom exception in this case, in contrast to other buckets
    $this->expectExceptionMessage('Unable to open local file for writing: vfs://vfs-test/not-writable-dir/file2.txt');
    parent::testVfsLocalDirNotWritableFilePull();
  }

  /**
   * @inheritDoc
   */
  public function testVfsLocalDirQuotaLimitedFilePull(): void
  {
    // SFTP bucket throws a custom exception in this case, in contrast to other buckets
    $this->expectExceptionMessage('Unable to write to local file: vfs://vfs-test/file.txt');
    parent::testVfsLocalDirQuotaLimitedFilePull();
  }



}
