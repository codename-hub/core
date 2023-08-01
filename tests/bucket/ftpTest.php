<?php

namespace codename\core\tests\bucket;

use codename\core\bucket;
use codename\core\bucket\ftp;
use codename\core\tests\helper;
use Exception;
use ReflectionException;

class ftpTest extends abstractBucketTest
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
        if (!gethostbynamel('unittest-ftp')) {
            static::markTestSkipped('FTP server unavailable, skipping.');
        }

        // wait for ftp server to come up
        if (!helper::waitForIt('unittest-ftp', 21, 3, 3, 5)) {
            static::fail('Failed to connect to ftp server');
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function testInvalidCredentials(): void
    {
        $this->expectExceptionMessage('EXCEPTION_BUCKET_FTP_LOGIN_FAILED');
        $this->getBucket([
          'basedir' => '/',
          'ftpserver' => [
            'host' => 'unittest-ftp',
            'port' => 21,
            'user' => 'invalid',
            'pass' => 'invalid',
          ],
        ]);
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
              'basedir' => '/',
              'ftpserver' => [
                'host' => 'unittest-ftp',
                'port' => 21,
                'user' => 'unittest-ftp-user',
                'pass' => 'unittest-ftp-pass',
              ],
            ];
        }

        return new ftp($config);
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

    /**
     * Tests connecting to a nonexisting host
     * @return void
     * @throws ReflectionException
     * @throws \codename\core\exception
     * @large
     */
    public function testConnectionFail(): void
    {
        $this->expectExceptionMessage('EXCEPTION_BUCKET_FTP_CONNECTION_FAILED');
        $this->getBucket([
          'basedir' => '/',
          'timeout' => 1, // smallest timeout possible
          'ftpserver' => [
              // try to connect to localhost - shouldn't give us an FTP server.
              // or you have one running locally...
            'host' => 'localhost',
            'port' => 21,
            'user' => 'random-user',
            'pass' => 'random-pass',
          ],
        ]);
    }
}
