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
   * @inheritDoc
   */
  public static function tearDownAfterClass(): void
  {
    // Reset instances to cleanup possible clients
    // e.g. database connections
    $_REQUEST['instances'] = [];
  }

  /**
   * allows setting the current environment config
   * @param array $config [description]
   */
  protected static function setEnvironmentConfig(array $config) {
    $configInstance = new \codename\core\config($config);
    overrideableApp::__overrideEnvironmentConfig($configInstance);
  }

  /**
   * creates a pseudo app instance
   * @return \codename\core\app
   */
  protected static function createApp(): \codename\core\app {
    return new overrideableApp();
  }

  /**
   * creates a model and builds it
   * @param  string       $schema [description]
   * @param  string       $model  [description]
   * @param  array        $config [description]
   * @param  callable|null  $initFunction
   * @return void
   */
  protected static function createModel(string $schema, string $model, array $config, ?callable $initFunction = null) {
    static::$models[$model] = [
      'schema'    => $schema,
      'model'     => $model,
      'config'    => $config,
      'initFunction' => $initFunction,
    ];
  }

  /**
   * [getModel description]
   * @param  string $model [description]
   * @return \codename\core\model
   */
  protected static function getModelStatic(string $model): \codename\core\model {
    $modelData = static::$models[$model];
    if($modelData['initFunction'] ?? false) {
      return $modelData['initFunction']($modelData['schema'], $modelData['model'], $modelData['config']);
    } else {
      return new sqlModel($modelData['schema'], $modelData['model'], $modelData['config']);
    }
  }

  /**
   * [getModel description]
   * @param  string               $model [description]
   * @return \codename\core\model        [description]
   */
  protected function getModel(string $model): \codename\core\model {
    return static::getModelStatic($model);
  }


  /**
   * Executes architect steps (building models/data structures)
   * @param  string $app     [description]
   * @param  string $vendor  [description]
   * @param  string $envName [description]
   * @return void
   */
  protected static function architect(string $app, string $vendor, string $envName) {
    $dbDoc = new overrideableDbDoc($app, $vendor);
    $architectEnv = new \codename\architect\config\environment(app::getEnvironment()->get(), $envName);

    $modeladapters = [];
    foreach(static::$models as $model) {
      $modeladapters[] = $dbDoc->getModelAdapter($model['schema'], $model['model'], $model['config'], $architectEnv);
    }

    // NOTE: if dbDoc fails due to misconfigured models,
    // this will fail here, too

    $dbDoc->setModelAdapters($modeladapters);

    $dbDoc->run(true, [ \codename\architect\dbdoc\task::TASK_TYPE_REQUIRED ]);
    $dbDoc->run(true, [ \codename\architect\dbdoc\task::TASK_TYPE_SUGGESTED ]);
  }

  /**
   * models in this environment/test case
   * @var array
   */
  protected static $models = [];

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
