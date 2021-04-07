<?php
namespace codename\core\tests;

/**
 * JSON Base model
 * enables freely defining and loading model configs
 * for static json data
 */
class jsonModel extends \codename\core\model\schemeless\json {
 /**
  * @inheritDoc
  */
 public function __CONSTRUCT($file, $prefix, $name, $config)
 {
   $this->useCache();
   $modeldata['appstack'] = \codename\core\app::getAppstack();
   $value = parent::__CONSTRUCT($modeldata);
   $this->config = new \codename\core\config($config);
   $this->setConfig($file, $prefix, $name);
   return $value;
 }

 /**
  * @inheritDoc
  */
 protected function loadConfig(): \codename\core\config
 {
   // has to be pre-set above
   return $this->config;
 }
}
