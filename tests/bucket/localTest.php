<?php

namespace codename\core\tests\bucket;

use codename\core\bucket;
use codename\core\bucket\local;
use codename\core\exception;
use FilesystemIterator;
use org\bovigo\vfs\vfsStream;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;

class localTest extends abstractBucketTest
{
    /**
     * Suffix to be used for local test bucket
     * @var null|string
     */
    protected static ?string $localTmpDir = null;

    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$localTmpDir = sys_get_temp_dir() . '/bucket-local-test-' . microtime(true) . '/';
    }

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        //
        // local tmp dir might be removed
        // if tests are executed in parallel or process-isolated.
        //
        if (is_dir(static::$localTmpDir)) {
            //
            // at this point, there _SHOULD_ be no file left, only (sub)dirs
            // try to remove them...
            //
            $it = new RecursiveDirectoryIterator(static::$localTmpDir, FilesystemIterator::SKIP_DOTS);
            $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($it as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                }
            }
            rmdir(static::$localTmpDir);
        }
    }

    /**
     * tests pushing to a local bucket
     * while having not enough disk space to do so.
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testRemoteQuotaLimited(): void
    {
        $bucketDir = $this->vfsRoot->url() . '/quota-limited/';
        mkdir($bucketDir, 0777, true);
        $bucket = $this->getBucket([
          'basedir' => $bucketDir,
          'public' => false,
        ]);
        vfsStream::setQuota(1);

        $this->expectException(exception::class);
        $this->expectExceptionMessage(local::EXCEPTION_FILEPUSH_FILEWRITABLE_UNKNOWN_ERROR);
        static::assertFalse($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'pushed_file.ext'));
    }

    /**
     * {@inheritDoc}
     * @param array|null $config
     * @return bucket
     * @throws ReflectionException
     * @throws exception
     */
    public function getBucket(?array $config = null): bucket
    {
        if ($config === null) {
            //
            // Default test bucket
            //
            $config = [
                // Default config
              'basedir' => static::$localTmpDir,
              'public' => false,
            ];

            // create the local temp folder, if it doesn't exist yet.
            if (!is_dir($config['basedir'])) {
                mkdir($config['basedir'], 0777, true);
            }
        }

        return new local($config);
    }

    /**
     * {@inheritDoc}
     */
    public function testInvalidEmptyConfiguration(): void
    {
        parent::testInvalidEmptyConfiguration();
    }

    /**
     * {@inheritDoc}
     */
    public function testFileAvailableFalse(): void
    {
        parent::testFileAvailableFalse();
    }

    /**
     * {@inheritDoc}
     */
    public function testVfsLocalDirQuotaLimitedFilePull(): void
    {
        parent::testVfsLocalDirQuotaLimitedFilePull();
    }

    /**
     * {@inheritDoc}
     */
    public function testFilePushSuccessful(): void
    {
        parent::testFilePushSuccessful();
    }

    /**
     * {@inheritDoc}
     */
    public function testFilePushNestedSuccessful(): void
    {
        parent::testFilePushNestedSuccessful();
    }

    /**
     * {@inheritDoc}
     */
    public function testFilePushMaliciousDirUpFails(): void
    {
        parent::testFilePushMaliciousDirUpFails();
    }

    /**
     * {@inheritDoc}
     */
    public function testFilePushMaliciousDirTraversalFails(): void
    {
        parent::testFilePushMaliciousDirTraversalFails();
    }

    /**
     * {@inheritDoc}
     */
    public function testFilePushMaliciousMultipleDirTraversalFails(): void
    {
        parent::testFilePushMaliciousMultipleDirTraversalFails();
    }

    /**
     * {@inheritDoc}
     */
    public function testFilePushAlreadyExists(): void
    {
        parent::testFilePushAlreadyExists();
    }

    /**
     * {@inheritDoc}
     */
    public function testFilePullNonexisting(): void
    {
        parent::testFilePullNonexisting();
    }

    /**
     * {@inheritDoc}
     */
    public function testFilePushMissingLocalFile(): void
    {
        parent::testFilePullNonexisting();
    }

    /**
     * {@inheritDoc}
     */
    public function testFileAvailableOnDir(): void
    {
        parent::testFileAvailableOnDir();
    }

    /**
     * {@inheritDoc}
     */
    public function testFileDeleteNonexistent(): void
    {
        parent::testFileDeleteNonexistent();
    }

    /**
     * {@inheritDoc}
     */
    public function testFileMoveNonexistentFailed(): void
    {
        parent::testFileMoveNonexistentFailed();
    }

    /**
     * {@inheritDoc}
     */
    public function testFileMoveAlreadyExistsFailed(): void
    {
        parent::testFileMoveAlreadyExistsFailed();
    }

    /**
     * {@inheritDoc}
     */
    public function testFileMoveAlreadyExistsNestedFailed(): void
    {
        parent::testFileMoveAlreadyExistsNestedFailed();
    }

    /**
     * {@inheritDoc}
     */
    public function testDirListSuccessful(): void
    {
        parent::testDirListSuccessful();
    }

    /**
     * {@inheritDoc}
     */
    public function testIsFile(): void
    {
        parent::testIsFile();
    }

    /**
     * {@inheritDoc}
     */
    public function testDirListNestedSuccessful(): void
    {
        parent::testDirListNestedSuccessful();
    }

    /**
     * {@inheritDoc}
     */
    public function testDirAvailableOnFile(): void
    {
        parent::testDirAvailableOnFile();
    }

    /**
     * {@inheritDoc}
     */
    public function testDirListNonexisting(): void
    {
        parent::testDirListNonexisting();
    }

    /**
     * Emulates a not-writable remote target directory
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testRemoteNotWritable(): void
    {
        $bucketDir = $this->vfsRoot->url() . '/not-writable/';
        mkdir($bucketDir, 0600, true);
        $this->vfsRoot->getChild('not-writable')->chown('other-user');
        $bucket = $this->getBucket([
          'basedir' => $bucketDir,
          'public' => false,
        ]);
        $this->expectException(exception::class);
        $this->expectExceptionMessage(local::EXCEPTION_FILEPUSH_FILENOTWRITABLE);
        static::assertFalse($bucket->filePush(__DIR__ . '/testdata/testfile.ext', 'pushed_file.ext'));
    }
}
