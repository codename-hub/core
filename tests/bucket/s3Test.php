<?php
namespace codename\core\tests\bucket;

use codename\core\tests\bucket\abstractBucketTest;

class s3Test extends abstractBucketTest {

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
        //
        'bucket' => 'fakes3',

        // NOTE: if bucket_endpoint is used, we NEED to hardcode stuff:
        // (bucket inside endpoint)
        // 'bucket_endpoint' => true,
        // 'endpoint' => 'http://fakes3.unittest-s3:4569',

        // Alternative, but requires hostnames to be available
        // (e.g. bucketname.unittest-s3)
        'bucket_endpoint' => false,
        'endpoint' => 'http://unittest-s3:4569',


        'credentials' => [
          'key' => 'dummy',
          'secret' => 'dummy',
        ],
        'prefix' => null,
        'region' => null
        // 'public'  => false,
      ];
    }

    return new \codename\core\bucket\s3($config);
  }

  /**
   * @inheritDoc
   */
  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();

    // wait for S3 to come up
    if(!\codename\core\tests\helper::waitForIt('unittest-s3', 4569, 3, 3, 5)) {
      throw new \Exception('Failed to connect to S3 server');
    }
  }
}
