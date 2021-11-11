<?php
namespace codename\core\model\schemeless;

use \codename\core\app;

/**
 * model for wrapping classes / loadable modules
 */
abstract class abstractModelModuleLoader extends \codename\core\model\schemeless\json implements \codename\core\model\modelInterface {

  /**
   * [setClassConfig description]
   * @param string $baseName  [description]
   * @param string $baseClass [description]
   * @param string $namespace [description]
   * @param string $baseDir   [description]
   */
  protected function setClassConfig(string $baseName, string $baseClass, string $namespace, string $baseDir) {
    $this->config = new \codename\core\config([
      'base_name'    => $baseName,
      'base_class'   => $baseClass,
      'namespace'    => $namespace,
      'base_dir'     => $baseDir
    ]);
  }

  /**
   * @inheritDoc
   */
  protected function loadConfig(): \codename\core\config
  {
    return new \codename\core\config([]);
  }

  /**
   * @inheritDoc
   */
  public function getPrimarykey() : string
  {
    return 'module_name';
  }

  /**
   * @inheritDoc
   */
  protected function internalQuery(string $query, array $params = array())
  {
    $classes = \codename\core\helper\classes::getImplementationsInNamespace(
      $this->config->get('base_class'),
      $this->config->get('namespace'),
      $this->config->get('base_dir')
    );

    $translateInstance = app::getTranslate();

    $result = [];

    foreach($classes as $r) {
      $name = $r['name'];
      $class = app::getInheritedClass($this->config->get('base_name').'_'.$name);
      $reflectionClass = (new \ReflectionClass($class));

      $displayName = null;
      if($reflectionClass->implementsInterface('\\codename\\core\\model\\schemeless\\moduleLoaderInterface')) {
        $displayName = $translateInstance->translate($class::getTranslationKey());
      } else {
        $displayName = $name;
      }

      $result[$name] = [
        'module_name' => $name,
        'module_displayname' => $displayName
      ];
    }

    if(count($this->filter) > 0) {
        $result = $this->filterResults($result);
    }

    return $result;
  }

  /**
   * [getModuleClass description]
   * @param  string $value [description]
   * @return string        [description]
   */
  public function getModuleClass(string $value) : string {
    return app::getInheritedClass($this->config->get('base_name').'_'.$value);
  }

}
