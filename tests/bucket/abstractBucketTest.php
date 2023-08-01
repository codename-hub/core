<?php

namespace codename\core\tests\bucket;

use codename\core\bucket;
use codename\core\exception;
use codename\core\tests\base;
use org\bovigo\vfs\Quota;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamException;
use ReflectionException;

abstract class abstractBucketTest extends base
{
    /**
     * VFS for helping with mocking erroneous storage
     * @var null|vfsStreamDirectory
     */
    protected ?vfsStreamDirectory $vfsRoot = null;

    /**
     * @return void
     */
    protected function testInvalidEmptyConfiguration(): void
    {
        $this->expectException(exception::class);
        // Simply pass an empty configuration array
        $this->getBucket([]);
    }

    /**
     * [getBucket description]
     * @param array|null $config
     * @return bucket [description]
     */
    abstract public function getBucket(?array $config = null): bucket;

    /**
     * @return void
     */
    protected function testFileAvailableFalse(): void
    {
        $bucket = $this->getBucket();
        static::assertFalse($bucket->fileAvailable('non-existing-file'));
    }

    /**
     * Tests file availability in the bucket
     * NOTE: needs to be placed, first!
     * @return void
     */
    protected function testFileAvailableTrue(): void
    {
        $bucket = $this->getBucket();
        static::assertTrue($bucket->fileAvailable('testfile.ext'));
    }

    /**
     * makes sure VFS works
     * and tries to pull a file to a directory
     * where we have no access to.
     * @return void
     */
    protected function testVfsLocalDirNotWritableFilePull(): void
    {
        $bucket = $this->getBucket();

        $writableLocalDir = $this->vfsRoot->url() . '/writable-dir';
        mkdir($writableLocalDir, 0777, true);
        $writableLocalFile = $writableLocalDir . '/file1.txt';
        static::assertTrue($bucket->filePull('testfile.ext', $writableLocalFile));

        //
        // Emulate dir that is owned by another user
        // and not writable for the current one.
        //
        $notWritablePath = $this->vfsRoot->url() . '/not-writable-dir';
        mkdir($notWritablePath, 0600, true);
        $notWritableDir = $this->vfsRoot->getChild('not-writable-dir');
        $notWritableDir->chown('other-user');

        $notWritableLocalFile = $notWritablePath . '/file2.txt';
        static::assertFalse($bucket->filePull('testfile.ext', $notWritableLocalFile));
    }

    /**
     * limits local VFS's quota (disk space)
     * and tries to pull a file
     * @return void
     */
    protected function testVfsLocalDirQuotaLimitedFilePull(): void
    {
        vfsStream::setQuota(1); // ultra-low quota
        $bucket = $this->getBucket();
        $localFileTarget = $this->vfsRoot->url() . '/file.txt';
        static::assertFalse($bucket->filePull('testfile.ext', $localFileTarget));
    }

    /**
     * @return void
     */
    protected function testFilePushSuccessful(): void
    {
        $bucket = $this->getBucket();
        static::assertTrue($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'pushed_file.ext'));
        static::assertTrue($bucket->fileDelete('pushed_file.ext'));
    }

    /**
     * @return void
     */
    protected function testFilePushNestedSuccessful(): void
    {
        $bucket = $this->getBucket();
        static::assertTrue($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'nested1/nested2/pushed_file.ext'));
        static::assertTrue($bucket->fileDelete('nested1/nested2/pushed_file.ext'));
    }

    /**
     * Tries to go up one dir (like cd ..)
     * but in the 'allowed' space - anyway, this should not be possible.
     * @return void
     */
    protected function testFilePushMaliciousDirUpFails(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage(bucket::BUCKET_EXCEPTION_BAD_PATH);
        $bucket = $this->getBucket();
        static::assertFalse($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'nested1/../pushed_file.ext'));
    }

    /**
     * @return void
     */
    protected function testFilePushMaliciousDirTraversalFails(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage(bucket::BUCKET_EXCEPTION_BAD_PATH);
        $bucket = $this->getBucket();
        static::assertFalse($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'nested1/../../pushed_file.ext'));
    }

    /**
     * @return void
     */
    protected function testFilePushMaliciousMultipleDirTraversalFails(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessage(bucket::BUCKET_EXCEPTION_BAD_PATH);
        $bucket = $this->getBucket();
        static::assertFalse($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'nested1/../../../pushed_file.ext'));
    }

    /**
     * @return void
     */
    protected function testFilePushAlreadyExists(): void
    {
        $bucket = $this->getBucket();
        static::assertTrue($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'pushed_file_existing.ext'));
        static::assertFalse($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'pushed_file_existing.ext'));
        static::assertTrue($bucket->fileDelete('pushed_file_existing.ext'));
    }

    /**
     * @return void
     */
    protected function testFilePullSuccessful(): void
    {
        $bucket = $this->getBucket();
        $pulledPath = sys_get_temp_dir() . '/testFilePullSuccessful';
        static::assertTrue($bucket->filePull('testfile.ext', $pulledPath));
        static::assertEquals(file_get_contents(__DIR__ . '/testdata/testfile.ext'), file_get_contents($pulledPath));
        static::assertTrue(unlink($pulledPath));
    }

    /**
     * @return void
     */
    protected function testFilePullAlreadyExists(): void
    {
        $bucket = $this->getBucket();
        $pulledPath = sys_get_temp_dir() . '/testFilePullAlreadyExists';
        static::assertTrue($bucket->filePull('testfile.ext', $pulledPath));
        static::assertFalse($bucket->filePull('testfile.ext', $pulledPath));
        static::assertTrue(unlink($pulledPath));
    }

    /**
     * @return void
     */
    protected function testFilePullNonexisting(): void
    {
        $bucket = $this->getBucket();
        $pulledPath = sys_get_temp_dir() . '/testFilePullNonexisting';
        static::assertFalse($bucket->filePull('nonexisting.ext', $pulledPath));
    }

    /**
     * @return void
     */
    protected function testFilePushMissingLocalFile(): void
    {
        $bucket = $this->getBucket();
        static::assertFalse($bucket->filePush(__DIR__ . '/testdata/nonexisting-file.ext', 'pushed_file2.ext'));
    }

    /**
     * Tests file moving in the bucket
     * @return void
     */
    protected function testFileMoveSuccess(): void
    {
        $bucket = $this->getBucket();
        static::assertTrue($bucket->fileMove('testfile.ext', 'testfile_moved.ext'));
        static::assertTrue($bucket->fileMove('testfile_moved.ext', 'testfile.ext'));
    }

    /**
     * Tests fileMove into a subdir
     * NOTE: might leave garbage data (e.g. subdir) in the bucket
     * @return void
     */
    protected function testFileMoveNestedSuccess(): void
    {
        $bucket = $this->getBucket();
        static::assertTrue($bucket->fileMove('testfile.ext', 'subdir/testfile_moved.ext'));
        static::assertTrue($bucket->fileMove('subdir/testfile_moved.ext', 'testfile.ext'));
    }

    /**
     * @return void
     */
    protected function testFileAvailableOnDir(): void
    {
        $bucket = $this->getBucket();
        // push a file first to create the dir implicitly
        static::assertTrue($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'some-dir/file.ext'));

        // test for fileAvailable on the directory
        // should return false
        static::assertFalse($bucket->fileAvailable('some-dir'));

        // delete it afterwards
        static::assertTrue($bucket->fileDelete('some-dir/file.ext'));
    }

    /**
     * Tests try to delete nonexistent file
     * NOTE: bucket behaviour is defined to return TRUE
     * if the respective object (file) does not exist.
     * @return void
     */
    protected function testFileDeleteNonexistent(): void
    {
        $bucket = $this->getBucket();
        static::assertTrue($bucket->fileDelete('non-existent.ext'));
    }

    /**
     * Tests try to move a nonexistent file
     * @return void
     */
    protected function testFileMoveNonexistentFailed(): void
    {
        $bucket = $this->getBucket();
        static::assertFalse($bucket->fileMove('non-existent.ext', 'non-existent2.ext'));
    }

    /**
     * @return void
     */
    protected function testFileMoveAlreadyExistsFailed(): void
    {
        $bucket = $this->getBucket();
        static::assertTrue($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'filemove_already_exists_test.ext'));
        static::assertTrue($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'testfile_moveme.ext'));
        // try moving (renaming) to a location that already exists
        static::assertFalse($bucket->fileMove('testfile_moveme.ext', 'filemove_already_exists_test.ext'));
        static::assertTrue($bucket->fileDelete('filemove_already_exists_test.ext'));
        static::assertTrue($bucket->fileDelete('testfile_moveme.ext'));
    }

    /**
     * @return void
     */
    protected function testFileMoveAlreadyExistsNestedFailed(): void
    {
        $bucket = $this->getBucket();
        static::assertTrue($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'nested/filemove_already_exists_nested_test.ext'));
        static::assertTrue($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'nested2/testfile_nested_moveme.ext'));
        // try moving (renaming) to a location that already exists
        static::assertFalse($bucket->fileMove('nested2/testfile_nested_moveme.ext', 'nested/filemove_already_exists_nested_test.ext'));
        static::assertTrue($bucket->fileDelete('nested/filemove_already_exists_nested_test.ext'));
        static::assertTrue($bucket->fileDelete('nested2/testfile_nested_moveme.ext'));
    }

    /**
     * @return void
     */
    protected function testDirListSuccessful(): void
    {
        $bucket = $this->getBucket();

        static::assertTrue($bucket->dirAvailable(''));
        $res = $bucket->dirList('');

        // We expect at least one file
        // 'subdir' might be unavailable, if a test failed
        static::assertGreaterThanOrEqual(1, count($res));

        foreach ($res as $r) {
            if ($r == 'subdir') {
                static::assertFalse($bucket->isFile($r));
            } elseif ($r == 'testfile.ext') {
                static::assertTrue($bucket->isFile($r));
            }
        }
    }

    /**
     * @return void
     */
    protected function testIsFile(): void
    {
        $bucket = $this->getBucket();
        $bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'test-is-file/file.ext');
        static::assertFalse($bucket->isFile('test-is-file'));
        static::assertTrue($bucket->isFile('test-is-file/file.ext'));
        $bucket->fileDelete('test-is-file/file.ext');
    }

    /**
     * @return void
     */
    protected function testDirListNestedSuccessful(): void
    {
        $bucket = $this->getBucket();

        // first, place a file in the subdir
        static::assertTrue($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'subdir/testDirListNestedSuccessful'));

        static::assertTrue($bucket->dirAvailable('subdir'));
        $res = $bucket->dirList('subdir');

        static::assertCount(1, $res);
        static::assertEquals('subdir/testDirListNestedSuccessful', $res[0]);

        // delete the test file afterward
        static::assertTrue($bucket->fileDelete('subdir/testDirListNestedSuccessful'));
    }

    /**
     * @return void
     */
    protected function testDirAvailableOnFile(): void
    {
        $bucket = $this->getBucket();
        static::assertFalse($bucket->dirAvailable('testfile.ext'));
    }

    /**
     * @return void
     */
    protected function testDirListNonexisting(): void
    {
        $bucket = $this->getBucket();
        static::assertFalse($bucket->dirAvailable('nonexisting'));
        $res = $bucket->dirList('nonexisting');
        static::assertEmpty($res);
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws vfsStreamException
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

        // init test files
        $bucket = $this->getBucket();
        if(!$bucket->filePush(__DIR__.'/testdata/testfile.ext', 'testfile.ext')) {
            static::fail('Initial test setup failed');
        }

        // create a VFS for testing various things
        // e.g. erroneous local storage location
        // or (if applicable) broken 'remote' storage (local bucket)
        $this->vfsRoot = vfsStream::setup('vfs-test');
        vfsStream::setQuota(Quota::UNLIMITED);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        $this->getBucket()->fileDelete('testfile.ext');
    }
}
