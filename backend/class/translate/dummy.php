<?php
namespace codename\core\translate;

use \codename\core\app;
use \codename\core\exception;

class dummy extends json {

    /**
     * [getSourceInstance description]
     * @param  string                $name [description]
     * @return \codename\core\config|null            [description]
     */
    protected function getSourceInstance(string $name) : ?\codename\core\config {
      $stackname = 'translation/' . $this->getPrefix() . '/' . $name . '.json';
      if(array_key_exists($stackname, $this->instances)) {
        return $this->instances[$stackname];
      } else {
        $this->instances[$stackname] = new \codename\core\config([]);
      }
      return $this->instances[$stackname];
    }

    /**
     * @inheritDoc
     */
    public function getAllTranslations(string $prefix): ?array
    {
      return $this->getSourceInstance($prefix) ? $this->getSourceInstance($prefix)->get() : null;
    }

    /**
     * @inheritDoc
     */
    public function getPrefix(): string
    {
      return app::getRequest()->getData('lang');
    }

}
