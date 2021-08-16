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


}
