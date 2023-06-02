<?php

namespace codename\core\tests\bucket;

use codename\core\bucket;
use codename\core\bucket\s3;
use codename\core\tests\helper;
use Exception;
use ReflectionException;

class s3Test extends abstractBucketTest
{
    /**
     * {@inheritDoc}
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Preliminary check, if DNS is not available
        // we simply assume there's no host for testing, skip.
        if (!gethostbynamel('unittest-s3')) {
            static::markTestSkipped('S3 server unavailable, skipping.');
        }

        // wait for S3 to come up
        if (!helper::waitForIt('unittest-s3', 4569, 3, 3, 5)) {
            static::fail('Failed to connect to S3 server');
        }
    }

    /**
     * {@inheritDoc}
     * @param array|null $config
     * @return bucket
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function getBucket(?array $config = null): bucket
    {
        if ($config === null) {
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
              'region' => null,
            ];
        }

        return new s3($config);
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
    public function testFileAvailableTrue(): void
    {
        parent::testFileAvailableTrue();
    }

    /**
     * {@inheritDoc}
     */
    public function testVfsLocalDirNotWritableFilePull(): void
    {
        parent::testVfsLocalDirNotWritableFilePull();
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
    public function testFilePullSuccessful(): void
    {
        parent::testFilePullSuccessful();
    }

    /**
     * {@inheritDoc}
     */
    public function testFilePullAlreadyExists(): void
    {
        parent::testFilePullAlreadyExists();
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
    public function testFileMoveSuccess(): void
    {
        parent::testFileMoveSuccess();
    }

    /**
     * {@inheritDoc}
     */
    public function testFileMoveNestedSuccess(): void
    {
        parent::testFileMoveNestedSuccess();
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
}
