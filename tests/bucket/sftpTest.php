<?php

namespace codename\core\tests\bucket;

use codename\core\bucket;
use codename\core\bucket\sftp;
use codename\core\exception;
use codename\core\sensitiveException;
use codename\core\tests\helper;
use ReflectionException;

class sftpTest extends abstractBucketTest
{
    protected static array $instances = [];

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Preliminary check, if DNS is not available
        // we simply assume there's no host for testing, skip.
        if (!gethostbynamel('unittest-sftp')) {
            static::markTestSkipped('SFTP server unavailable, skipping.');
        }

        // wait for rmysql to come up
        if (!helper::waitForIt('unittest-sftp', 22, 3, 3, 5)) {
            static::fail('Failed to connect to sftp server');
        }
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     * @throws sensitiveException
     */
    public function testUnreachableHost(): void
    {
        $this->expectException(exception::class);
        $this->expectExceptionMessageMatches('/EXCEPTION_BUCKET_SFTP_SSH_CONNECTION_FAILED|ssh2_connect\(\)\: php_network_getaddresses: getaddrinfo for nonexisting-sftp failed: (?:Name or service not known|Temporary failure in name resolution)/');

        $this->getBucket([
            // Default config
          'basedir' => '/share/',
          'sftpserver' => [
            'host' => 'nonexisting-sftp',
            'port' => 22,
            'auth_type' => 'password',
            'user' => 'unittest-sftp-user-auth-pw',
            'pass' => 'unittest-sftp-user-pass',
          ],
        ]);
    }

    /**
     * {@inheritDoc}
     * @param array|null $config
     * @return bucket
     * @throws ReflectionException
     * @throws exception
     * @throws sensitiveException
     */
    public function getBucket(?array $config = null): bucket
    {
        // print_r([ 'getBucket' => $config ]);
        if ($config === null) {
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
                'pass' => 'unittest-sftp-user-pass',
              ],
                // 'public'  => false,
            ];
        }

        $hash = md5(serialize($config));

        if (!(static::$instances[$hash] ?? false)) {
            static::$instances[$hash] = new sftp($config);
        }
        return static::$instances[$hash];
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
     * {@inheritDoc}
     */
    public function testVfsLocalDirNotWritableFilePull(): void
    {
        // SFTP bucket throws a custom exception in this case, in contrast to other buckets
        $this->expectExceptionMessage('Unable to open local file for writing: vfs://vfs-test/not-writable-dir/file2.txt');
        parent::testVfsLocalDirNotWritableFilePull();
    }

    /**
     * {@inheritDoc}
     */
    public function testVfsLocalDirQuotaLimitedFilePull(): void
    {
        // SFTP bucket throws a custom exception in this case, in contrast to other buckets
        $this->expectExceptionMessage('Unable to write to local file: vfs://vfs-test/file.txt');
        parent::testVfsLocalDirQuotaLimitedFilePull();
    }
}
