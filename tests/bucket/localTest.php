<?php
namespace codename\core\tests\bucket;

use codename\core\tests\bucket\abstractBucketTest;

class localTest extends abstractBucketTest {

  /**
   * Suffix to be used for local test bucket
   * @var string
   */
  protected static $localTmpDir = null;

  /**
   * @inheritDoc
   */
  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();
    static::$localTmpDir = sys_get_temp_dir() . '/bucket-local-test-'.microtime(true).'/';
  }

  /**
   * @inheritDoc
   */
  public static function tearDownAfterClass(): void
  {
    parent::tearDownAfterClass();

    //
    // local tmp dir might be removed
    // if tests are executed in parallel or process-isolated.
    //
    if(is_dir(static::$localTmpDir)) {
      //
      // at this point, there _SHOULD_ be no file left, only (sub)dirs
      // try to remove them...
      //
      $it = new \RecursiveDirectoryIterator(static::$localTmpDir, \FilesystemIterator::SKIP_DOTS);
      $it = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
      foreach($it as $file) {
        if ($file->isDir()) rmdir($file->getPathname());
      }
      rmdir(static::$localTmpDir);
    }
  }

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
        'basedir' => static::$localTmpDir,
        'public'  => false,
      ];

      // create the local temp folder, if it doesn't exist yet.
      if(!is_dir($config['basedir'])) {
        mkdir($config['basedir'], 0777, true);
      }
    }

    return new \codename\core\bucket\local($config);
  }

  /**
   * tests pushing to a local bucket
   * while having not enough disk space to do so.
   */
  public function testRemoteQuotaLimited(): void {
    $bucketDir = $this->vfsRoot->url() . '/quota-limited/';
    mkdir($bucketDir, 0777, true);
    $bucket = $this->getBucket([
      'basedir' => $bucketDir,
      'public'  => false
    ]);
    \org\bovigo\vfs\vfsStream::setQuota(1);

    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage(\codename\core\bucket\local::EXCEPTION_FILEPUSH_FILEWRITABLE_UNKNOWN_ERROR);
    $this->assertFalse($bucket->filePush(__DIR__.'/testdata/testfile.ext', 'pushed_file.ext'));
  }

  /**
   * Emulates a not-writable remote target directory
   */
  public function testRemoteNotWritable(): void {
    $bucketDir = $this->vfsRoot->url() . '/not-writable/';
    mkdir($bucketDir, 0600, true);
    $this->vfsRoot->getChild('not-writable')->chown('other-user');
    $bucket = $this->getBucket([
      'basedir' => $bucketDir,
      'public'  => false
    ]);
    $this->expectException(\codename\core\exception::class);
    $this->expectExceptionMessage(\codename\core\bucket\local::EXCEPTION_FILEPUSH_FILENOTWRITABLE);
    $this->assertFalse($bucket->filePush(__DIR__.'/testdata/testfile.ext', 'pushed_file.ext'));
  }

}
