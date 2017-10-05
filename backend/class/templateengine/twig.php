<?php
namespace codename\core\templateengine;
use \codename\core\app;
use \codename\core\exception;

/**
 * [twig description]
 */
class twig extends \codename\core\templateengine {

  /**
   * twig instance
   * @var \Twig\Environment
   */
  protected $twigInstance = null;

  /**
   * twig loader
   * @var \Twig\Loader\LoaderInterface
   */
  protected $twigLoader = null;

  /**
   * @inheritDoc
   */
  public function __construct(array $config = array())
  {
    // Check for existance of Twig Classes.
    if (!class_exists('\\Twig\\Environment')) {
      throw new exception("CORE_TEMPLATEENGINE_TWIG_CLASS_DOES_NOT_EXIST", exception::$ERRORLEVEL_FATAL);
    }

    parent::__construct($config);
    $paths = array();

    // add current app home frontend to paths
    $paths[] = app::getHomedir() . 'frontend/';

    // collect appstack paths
    foreach(app::getAppstack() as $parentapp) {
      $vendor = $parentapp['vendor'];
      $app = $parentapp['app'];
      $filename = CORE_VENDORDIR . $vendor . '/' . $app . '/' . 'frontend/';
    }

    $this->twigLoader = new \Twig\Loader\FilesystemLoader($paths, CORE_VENDORDIR);
    $this->twigInstance = new \Twig\Environment($this->twigLoader, $config['environment'] ?? array());
  }

  /**
   * @inheritDoc
   */
  public function renderView(string $viewPath, \codename\core\datacontainer $data) : string {
    $twigTemplate = $this->twigInstance->load('view/' . $data->getData('context') . '/' . $viewPath . '.html.twig');
    return $twigTemplate->render($data->getData());
  }

  /**
   * @inheritDoc
   */
  public function renderTemplate(string $templatePath, \codename\core\datacontainer $data) : string {
    $twigTemplate = $this->twigInstance->load('template/' . $templatePath . '/template.html.twig');
    return $twigTemplate->render($data->getData());
  }

}