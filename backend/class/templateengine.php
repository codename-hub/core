<?php
namespace codename\core;
use \codename\core\app;
use \codename\core\datacontainer;

/**
 * [templateengine description]
 */
abstract class templateengine {

  /**
   * config
   * @var config
   */
  protected $config = null;

  /**
   * name of config validator for this template engine
   * @var string|null
   */
  protected $configValidator = null;

  /**
   * [__construct description]
   * @param array $config [description]
   */
  public function __construct(array $config = array())
  {
    // validate config on need
    if($this->configValidator != null) {
      $validator = app::getValidator($this->configValidator);
      if(count($errors = $validator->validate($config)) > 0) {
        throw new exception("CORE_TEMPLATEENGINE_CONFIG_VALIDATION_FAILED", exception::$ERRORLEVEL_FATAL, $config);
      }
    }

    $this->config = new config($config);
  }

  /**
   * Returns the path for storing (temporary) assets
   * for rendering or output
   * @return string [description]
   */
  public function getAssetsPath(): string {
    throw new \LogicException('Not implemented');
  }

  /**
   * [getConfig description]
   * @return config [description]
   */
  public function getConfig(): config {
    return $this->config;
  }

  /**
   * [render description]
   * @param  string                  $referencePath [path to view, without file extension]
   * @param  datacontainer $data     [data container / data context]
   * @return string                  [rendered view]
   */
  public abstract function render(string $referencePath, $data = null) : string;

  /**
   * [renderView description]
   * @param  string                  $viewPath [path to view, without file extension]
   * @param  datacontainer $data     [data container / data context]
   * @return string                  [rendered view]
   */
  public abstract function renderView(string $viewPath, $data = null) : string;

  /**
   * [renderTemplate description]
   * @param  string                      $templatePath [path to template, without file extension]
   * @param  datacontainer $data         [data container / data context]
   * @return string                      [rendered template]
   */
  public abstract function renderTemplate(string $templatePath, $data = null) : string;

}
