<?php
namespace codename\core\config\json;

use \codename\core\app;

/**
 * JSON Configuration that supports extending (via "extends" key)
 */
class extendable extends \codename\core\config\json {

  /**
   * @inheritDoc
   *
   * @param  string       $file        [description]
   * @param  bool         $appstack    [description]
   * @param  bool         $inherit     [description]
   * @param  array|null   $useAppstack [description]
   * @return extendable
   */
  public function __CONSTRUCT(string $file, bool $appstack = false, bool $inherit = false, ?array $useAppstack = null) {

    // do NOT start with an empty array
    // $config = array();
    $config = null;

    if(!$inherit && !$appstack) {
        $config = $this->decodeFile($this->getFullpath($file, $appstack));
        $config = $this->provideExtends($config, $appstack, $inherit, $useAppstack);
        $this->data = $config;
        return $this;
    }

    if($inherit && !$appstack) {
        throw new \codename\core\exception(self::EXCEPTION_CONSTRUCT_INVALIDBEHAVIOR, \codename\core\exception::$ERRORLEVEL_FATAL, array('file' => $fullpath, 'info' => 'Need Appstack to inherit config!'));
    }

    if($useAppstack == null) {
      $useAppstack = app::getAppstack();
    }

    foreach(array_reverse($useAppstack) as $app) {
        if(realpath($file) !== false) {
          $fullpath = $file;
        } else {
          $fullpath = app::getHomedir($app['vendor'], $app['app']) . $file;
        }
        if(!app::getInstance('filesystem_local')->fileAvailable($fullpath)) {
            continue;
        }

        // initialize config as empty array here
        // as this is the first found file in the hierarchy
        if($config === null) {
          $config = array();
        }

        $thisConf = $this->decodeFile($fullpath);
        $thisConf = $this->provideExtends($thisConf, $appstack, $inherit, $useAppstack);
        $this->inheritance[] = $fullpath;
        if($inherit) {
          $config = array_replace_recursive($config, $thisConf);
        } else {
          $config = $thisConf;
          break;
        }
    }

    if($config === null) {
      // config was not initialized during hierarchy traversal
      throw new \codename\core\exception(self::EXCEPTION_CONFIG_JSON_CONSTRUCT_HIERARCHY_NOT_FOUND, \codename\core\exception::$ERRORLEVEL_FATAL, array('file' => $file, 'appstack' => $useAppstack));
    }

    $this->data = $config;
    return $this;

  }


  /**
   * [provideExtends description]
   * @param  array|null  $config      [description]
   * @param  bool $appstack    [description]
   * @param  bool $inherit     [description]
   * @param  array|null  $useAppstack [description]
   * @return array|null               [description]
   */
  protected function provideExtends(?array $config, bool $appstack = false, bool $inherit = false, ?array $useAppstack = null) : ?array
  {
    if($config !== null && ($config['extends'] ?? false)) {
      $extends = is_array($config['extends']) ? $config['extends'] : [ $config['extends'] ];
      foreach($extends as $extend) {
        $extendableJsonConfig = new \codename\core\config\json\extendable($extend, $appstack, $inherit, $useAppstack);
        $config = array_replace_recursive($config, $extendableJsonConfig->get());
      }
    }
    if($config !== null && ($config['mixins'] ?? false)) {
      $mixins = is_array($config['mixins']) ? $config['mixins'] : [ $config['mixins'] ];
      foreach($mixins as $mixin) {
        $extendableJsonConfig = new \codename\core\config\json\extendable($mixin, $appstack, $inherit, $useAppstack);
        $config = array_merge_recursive($config, $extendableJsonConfig->get());
      }
    }
    return $config;
  }
}
