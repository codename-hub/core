<?php
namespace codename\core\tests\bucket;

use codename\core\tests\base;

abstract class abstractBucketTest extends base {

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

    // init test files
    $bucket = $this->getBucket();
    if(!$bucket->filePush(__DIR__.'/testdata/testfile.ext', 'testfile.ext')) {
      $this->addWarning('Initial test setup failed');
    }

    // create a VFS for testing various things
    // e.g. erroneous local storage location
    // or (if applicable) broken 'remote' storage (local bucket)
    $this->vfsRoot = \org\bovigo\vfs\vfsStream::setup('vfs-test');
    \org\bovigo\vfs\vfsStream::setQuota(\org\bovigo\vfs\Quota::UNLIMITED);
  }

  /**
   * VFS for helping with mocking erroneous storage
   * @var \org\bovigo\vfs\vfsStreamDirectory
   */
  protected $vfsRoot = null;

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
   * makes sure VFS works
   * and tries to pull a file to a directory
   * where we have no access to.
   */
  public function testVfsLocalDirNotWritableFilePull(): void {
    $bucket = $this->getBucket();

    $writableLocalDir = $this->vfsRoot->url() . '/writable-dir';
    mkdir($writableLocalDir, 0777, true);
    $writableLocalFile = $writableLocalDir . '/file1.txt';
    $this->assertTrue($bucket->filePull('testfile.ext', $writableLocalFile));

    //
    // Emulate dir that is owned by another user
    // and not writable for the current one.
    //
    $notWritablePath = $this->vfsRoot->url() . '/not-writable-dir';
    mkdir($notWritablePath, 0600, true);
    $notWritableDir = $this->vfsRoot->getChild('not-writable-dir');
    $notWritableDir->chown('other-user');

    $notWritableLocalFile = $notWritablePath . '/file2.txt';
    $this->assertFalse($bucket->filePull('testfile.ext', $notWritableLocalFile));
  }

  /**
   * limits local VFS's quota (disk space)
   * and tries to pull a file
   */
  public function testVfsLocalDirQuotaLimitedFilePull(): void {
    \org\bovigo\vfs\vfsStream::setQuota(1); // ultra-low quota
    $bucket = $this->getBucket();
    $localFileTarget = $this->vfsRoot->url() . '/file.txt';
    $this->assertFalse($bucket->filePull('testfile.ext', $localFileTarget));
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
   * [testFilePushAlreadyExists description]
   */
  public function testFilePushAlreadyExists(): void {
    $bucket = $this->getBucket();
    $this->assertTrue($bucket->filePush(__DIR__.'/testdata/testfile.ext', 'pushed_file_existing.ext'));
    $this->assertFalse($bucket->filePush(__DIR__.'/testdata/testfile.ext', 'pushed_file_existing.ext'));
    $this->assertTrue($bucket->fileDelete('pushed_file_existing.ext'));
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
   * [testFilePullAlreadyExists description]
   */
  public function testFilePullAlreadyExists(): void {
    $bucket = $this->getBucket();
    $pulledPath = sys_get_temp_dir().'/testFilePullAlreadyExists';
    $this->assertTrue($bucket->filePull('testfile.ext', $pulledPath));
    $this->assertFalse($bucket->filePull('testfile.ext', $pulledPath));
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
   * [testFileAvailableOnDir description]
   */
  public function testFileAvailableOnDir(): void {
    $bucket = $this->getBucket();
    // push a file first to create the dir implicitly
    $this->assertTrue($bucket->filePush(__DIR__.'/testdata/testfile.ext', 'some-dir/file.ext'));

    // test for fileAvailable on the directory
    // should return false
    $this->assertFalse($bucket->fileAvailable('some-dir'));

    // delete it afterwards
    $this->assertTrue($bucket->fileDelete('some-dir/file.ext'));
  }

  /**
   * Tests try to move a nonexistant file
   */
  public function testFileMoveNonexistantFailed(): void {
    $bucket = $this->getBucket();
    $this->assertFalse($bucket->fileMove('non-existant.ext', 'non-existant2.ext'));
  }

  /**
   * [testFileMoveAlreadyExistsFailed description]
   */
  public function testFileMoveAlreadyExistsFailed(): void {
    $bucket = $this->getBucket();
    $this->assertTrue($bucket->filePush(__DIR__.'/testdata/testfile.ext', 'filemove_already_exists_test.ext'));
    $this->assertTrue($bucket->filePush(__DIR__.'/testdata/testfile.ext', 'testfile_moveme.ext'));
    // try moving (renaming) to a location that already exists
    $this->assertFalse($bucket->fileMove('testfile_moveme.ext', 'filemove_already_exists_test.ext'));
    $this->assertTrue($bucket->fileDelete('filemove_already_exists_test.ext'));
    $this->assertTrue($bucket->fileDelete('testfile_moveme.ext'));
  }

  /**
   * [testFileMoveAlreadyExistsNestedFailed description]
   */
  public function testFileMoveAlreadyExistsNestedFailed(): void {
    $bucket = $this->getBucket();
    $this->assertTrue($bucket->filePush(__DIR__.'/testdata/testfile.ext', 'nested/filemove_already_exists_nested_test.ext'));
    $this->assertTrue($bucket->filePush(__DIR__.'/testdata/testfile.ext', 'nested2/testfile_nested_moveme.ext'));
    // try moving (renaming) to a location that already exists
    $this->assertFalse($bucket->fileMove('nested2/testfile_nested_moveme.ext', 'nested/filemove_already_exists_nested_test.ext'));
    $this->assertTrue($bucket->fileDelete('nested/filemove_already_exists_nested_test.ext'));
    $this->assertTrue($bucket->fileDelete('nested2/testfile_nested_moveme.ext'));
  }

  /**
   * [testDirListSuccessful description]
   */
  public function testDirListSuccessful(): void {
    $bucket = $this->getBucket();

    $this->assertTrue($bucket->dirAvailable(''));
    $res = $bucket->dirList('');

    // We expect at least one file
    // 'subdir' might be unavailable, if a another test failed
    $this->assertGreaterThanOrEqual(1, count($res));

    foreach($res as $r) {
      if($r == 'subdir') {
        $this->assertFalse($bucket->isFile($r));
      } else if($r == 'testfile.ext') {
        $this->assertTrue($bucket->isFile($r));
      } else {
        // $this->addWarning('Unexpected extra file/dir: ' . $r);
      }
    }
  }

  /**
   * [testDirListNestedSuccessful description]
   */
  public function testDirListNestedSuccessful(): void {
    $bucket = $this->getBucket();

    // first, place a file in the subdir
    $this->assertTrue($bucket->filePush(__DIR__.'/testdata/testfile.ext', 'subdir/testDirListNestedSuccessful'));

    $this->assertTrue($bucket->dirAvailable('subdir'));
    $res = $bucket->dirList('subdir');

    $this->assertCount(1, $res);
    $this->assertEquals('subdir/testDirListNestedSuccessful', $res[0]);

    // delete the test file afterwards
    $this->assertTrue($bucket->fileDelete('subdir/testDirListNestedSuccessful'));
  }

  /**
   * [testDirAvailableOnFile description]
   */
  public function testDirAvailableOnFile(): void {
    $bucket = $this->getBucket();
    $this->assertFalse($bucket->dirAvailable('testfile.ext'));

  }


  /**
   * [testDirListNonexisting description]
   */
  public function testDirListNonexisting(): void {
    $bucket = $this->getBucket();
    $this->assertFalse($bucket->dirAvailable('nonexisting'));
    $res = $bucket->dirList('nonexisting');
    $this->assertEmpty($res);

  }

}
