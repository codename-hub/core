<?php
namespace codename\core\templateengine;
use \codename\core\app;

/**
 * Simple Template Engine
 * for just using .php files (inline-code-based)
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
  public function render(string $referencePath, \codename\core\datacontainer $data): string {
    return app::parseFile(app::getInheritedPath("frontend/" . $referencePath . ".php"), $data->getData());
  }

  /**
   * @inheritDoc
   */
  public function renderView(string $viewPath, \codename\core\datacontainer $data): string {
    return $this->render("view/" . $data->getData('context') . "/" . $viewPath, $data);
  }

  /**
   * @inheritDoc
   */
  public function renderTemplate( string $templatePath, \codename\core\datacontainer $data): string {
    return $this->render("template/" . $templatePath . "/template.php", $data);
  }

}