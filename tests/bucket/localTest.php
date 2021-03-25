<?php
namespace codename\core\tests\bucket;

use codename\core\tests\bucket\abstractBucketTest;

class localTest extends abstractBucketTest {

  /**
   * @inheritDoc
   */
  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();
    static::$localTmpDir = sys_get_temp_dir() . '/bucket-local-test-'.time().'/';
  }

  /**
   * Suffix to be used for local test bucket
   * @var string
   */
  protected static $localTmpDir = null;

  /**
   * @inheritDoc
   */
  public function getBucket(?array $config = null): \codename\core\bucket
  {
    $config = $config ?? [
      // Default config
      'basedir' => static::$localTmpDir,
      'public'  => false,
    ];

    // create the local temp folder, if it doesn't exist yet.
    if(!is_dir($config['basedir'])) {
      mkdir($config['basedir'], 0777, true);
    }

    return new \codename\core\bucket\local($config);
  }

  /**
   * @inheritDoc
   */
  public static function tearDownAfterClass(): void
  {
    parent::tearDownAfterClass();
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
