<?php
namespace codename\core\templateengine;
use \codename\core\app;

/**
 * [simple description]
 */
class simple extends \codename\core\templateengine {

  /**
   * @inheritDoc
   */
  public function __construct(array $config = array())
  {
    parent::__construct($config);
  }

  /**
   * @inheritDoc
   */
  public function renderView(string $viewPath, \codename\core\datacontainer $data): string {
    return app::parseFile(app::getInheritedPath("frontend/view/" . $data->getData('context') . "/" . $viewPath . ".php"), $data->getData());
  }

  /**
   * @inheritDoc
   */
  public function renderTemplate( string $templatePath, \codename\core\datacontainer $data): string {
    return app::parseFile(app::getInheritedPath("frontend/template/" . $templatePath . "/template.php"), $data->getData());
  }

}