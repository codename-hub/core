<?php
namespace codename\core\tests\bucket;

use codename\core\tests\base;

abstract class abstractBucketTest extends base {

  /**
   * @inheritDoc
   */
  protected function setUp(): void
  {
    static::setEnvironmentConfig([
      'test' => [
        'filesystem' =>[
          'local' => [
            'driver' => 'local',
          ]
        ],
        'log' => [
          'debug' => [
            'driver' => 'system',
            'data' => [
              'name' => 'dummy'
            ]
          ]
        ],
      ]
    ]);

    // init test files
    $this->getBucket()->filePush(__DIR__.'/testdata/testfile.ext', 'testfile.ext');
  }

  /**
   * @inheritDoc
   */
  protected function tearDown(): void
  {
    $this->getBucket()->fileDelete('testfile.ext');
  }

  /**
   * [getBucket description]
   * @param  array|null $config
   * @return \codename\core\bucket [description]
   */
  public abstract function getBucket(?array $config = null): \codename\core\bucket;

  /**
  * [testInvalidEmptyConfiguration description]
  */
  public function testInvalidEmptyConfiguration(): void {
    $this->expectException(\codename\core\exception::class);
    // Simply pass an empty configuration array
    $bucket = $this->getBucket([]);
  }

  /**
   * [testFileAvailableFalse description]
   */
  public function testFileAvailableFalse(): void {
    $bucket = $this->getBucket();
    $this->assertFalse($bucket->fileAvailable('non-existing-file'));
  }

  /**
   * Tests file availability in the bucket
   * NOTE: needs to be placed, first!
   */
  public function testFileAvailableTrue(): void {
    $bucket = $this->getBucket();
    $this->assertTrue($bucket->fileAvailable('testfile.ext'));
  }

  /**
   * [testFilePush description]
   */
  public function testFilePushSuccessful(): void {
    $bucket = $this->getBucket();
    $this->assertTrue($bucket->filePush(__DIR__.'/testdata/testfile.ext', 'pushed_file.ext'));
    $this->assertTrue($bucket->fileDelete('pushed_file.ext'));
  }

  /**
   * [testFilePullSuccessful description]
   */
  public function testFilePullSuccessful(): void {
    $bucket = $this->getBucket();
    $pulledPath = sys_get_temp_dir().'/testFilePullSuccessful';
    $this->assertTrue($bucket->filePull('testfile.ext', $pulledPath));
    $this->assertEquals(file_get_contents(__DIR__.'/testdata/testfile.ext'), file_get_contents($pulledPath));
    $this->assertTrue(unlink($pulledPath));
  }

  /**
   * [testFilePullNonexisting description]
   */
  public function testFilePullNonexisting(): void {
    $bucket = $this->getBucket();
    $pulledPath = sys_get_temp_dir().'/testFilePullNonexisting';
    $this->assertFalse($bucket->filePull('nonexisting.ext', $pulledPath));
  }

  /**
   * [testFilePushMissingLocalFile description]
   */
  public function testFilePushMissingLocalFile(): void {
    $bucket = $this->getBucket();
    $this->assertFalse($bucket->filePush(__DIR__.'/testdata/nonexisting-file.ext', 'pushed_file2.ext'));
  }

  /**
   * Tests file moving in the bucket
   */
  public function testFileMoveSuccess(): void {
    $bucket = $this->getBucket();
    $this->assertTrue($bucket->fileMove('testfile.ext', 'testfile_moved.ext'));
    $this->assertTrue($bucket->fileMove('testfile_moved.ext', 'testfile.ext'));
  }

  /**
   * Tests fileMove into a subdir
   * NOTE: might leave garbage data (e.g. subdir) in the bucket
   */
  public function testFileMoveNestedSuccess(): void {
    $bucket = $this->getBucket();
    $this->assertTrue($bucket->fileMove('testfile.ext', 'subdir/testfile_moved.ext'));
    $this->assertTrue($bucket->fileMove('subdir/testfile_moved.ext', 'testfile.ext'));
  }

  /**
   * Tests try to move a nonexistant file
   */
  public function testFileMoveFailed(): void {
    $bucket = $this->getBucket();
    $this->assertFalse($bucket->fileMove('non-existant.ext', 'non-existant2.ext'));
  }

}
