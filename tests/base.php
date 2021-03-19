<?php
namespace codename\core\tests;

use codename\core\app;

/**
 * Base unit test class for using a core environment
 * @package codename\core
 * @since 2021-03-17
 */
abstract class base extends \PHPUnit\Framework\TestCase {

  /**
   * allows setting the current environment config
   * @param array $config [description]
   */
  protected function setEnvironmentConfig(array $config) {
    $configInstance = new \codename\core\config($config);
    overrideableApp::__overrideEnvironmentConfig($configInstance);
  }

  /**
   * creates a pseudo app instance
   * @return \codename\core\app
   */
  protected function createApp(): \codename\core\app {
    return new overrideableApp();
  }

  /**
   * creates a model and builds it
   * @param  string $schema [description]
   * @param  string $model  [description]
   * @param  array  $config [description]
   * @return void
   */
  protected function createModel(string $schema, string $model, array $config) {
    $this->models[$model] = [
      'schema' => $schema,
      'model'  => $model,
      'config' => $config,
    ];
  }

  /**
   * [getModel description]
   * @param  string $model [description]
   * @return \codename\core\model
   */
  protected function getModel(string $model): \codename\core\model {
    $modelData = $this->models[$model];
    return new sqlModel($modelData['schema'], $modelData['model'], $modelData['config']);
  }

  /**
   * Executes architect steps (building models/data structures)
   * @param  string $app     [description]
   * @param  string $vendor  [description]
   * @param  string $envName [description]
   * @return void
   */
  protected function architect(string $app, string $vendor, string $envName) {
    $dbDoc = new overrideableDbDoc('test', 'codename');
    $architectEnv = new \codename\architect\config\environment(app::getEnvironment()->get(), $envName);

    $modeladapters = [];
    foreach($this->models as $model) {
      $modeladapters[] = $dbDoc->getModelAdapter($model['schema'], $model['model'], $model['config'], $architectEnv);
    }

    $dbDoc->setModelAdapters($modeladapters);

    $dbDoc->run(true, [ \codename\architect\dbdoc\task::TASK_TYPE_REQUIRED ]);
    $dbDoc->run(true, [ \codename\architect\dbdoc\task::TASK_TYPE_SUGGESTED ]);
  }

  /**
   * models in this environment/test case
   * @var array
   */
  private $models = [];

}

/**
 * Class override that allows accessing protected or final methods
 * to emulate different environments or force specific circumstances
 */
class overrideableApp extends \codename\core\app {

  /**
   * @inheritDoc
   */
  public function __CONSTRUCT()
  {
    $value = parent::__CONSTRUCT();

    // TODO
    $this->injectApp([
      'vendor' => 'codename',
      'app' => 'architect',
      'namespace' => '\\codename\\architect'
    ]);
    return $value;
  }


  /**
   * Overrides/provides an environment config
   * for usage with custom test cases
   * @param \codename\core\config $config [description]
   */
  public static function __overrideEnvironmentConfig(\codename\core\config $config) {
    static::$environment = $config;
  }
}
