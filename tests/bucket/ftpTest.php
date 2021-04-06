<?php
namespace codename\core\tests\bucket;

use codename\core\tests\bucket\abstractBucketTest;

class ftpTest extends abstractBucketTest {

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
        'basedir' => '/',
        'ftpserver' => [
          'host' => 'unittest-ftp',
          'port' => 21,
          'user' => 'unittest-ftp-user',
          'pass' => 'unittest-ftp-pass',
          // 'passive_mode' => true,
          // 'ignore_passive_address' => true,
        ]
        // 'public'  => false,
      ];
    }

    return new \codename\core\bucket\ftp($config);
  }

  /**
   * @inheritDoc
   */
  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();

    // wait for rmysql to come up
    if(!\codename\core\tests\helper::waitForIt('unittest-ftp', 21, 3, 3, 5)) {
      throw new \Exception('Failed to connect to ftp server');
    }
  }
}
