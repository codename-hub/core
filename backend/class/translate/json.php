<?php
namespace codename\core\translate;

use \codename\core\app;
use \codename\core\exception;

class json extends \codename\core\translate implements \codename\core\translate\translateInterface {

    /**
     * I did not find the requested translation file.
     * @var string
     */
    CONST EXCEPTION_GETTRANSLATION_TRANSLATIONFILEMISSING = 'EXCEPTION_GETTRANSLATION_TRANSLATIONFILEMISSING';

    /**
     * The translation file that I managed to find was invalid.
     * @var string
     */
    CONST EXCEPTION_GETTRANSLATION_TRANSLATIONFILEINVALID = 'EXCEPTION_GETTRANSLATION_TRANSLATIONFILEINVALID';

    /**
     * json config reader instances
     * @var \codename\core\config\json[]
     */
    protected $instances = array();

    /**
     * translates a key in the format DATAFILE.SOMEKEY
     * Where the first key part (before the dot) is some kind of prefix
     * that is used for identifying the datasource fiel
     *
     * @param  string $key [description]
     * @return string      [description]
     */
    protected function getTranslation(string $key) : string {
        $keystr = $key;

        // Split into maximum of 2 elements (dots may exist afterwards)
        $key = explode('.', $key, 2);

        if(count($key) != 2) {
            throw new exception('EXCEPTION_TRANSLATE_JSON_MISSING_DOT', exception::$ERRORLEVEL_ERROR, $keystr);
        }

        $key[0] = strtolower($key[0]);
        $key[1] = strtoupper($key[1]);

        $instance = $this->getSourceInstance($key[0]);

        $keyname = $key[1];
        $value = $keyname;

        // use instance, if not null - otherwise, let it fall back.
        $v = $instance !== null ? $instance->get($key[1]) : null;
        if($v != null) {
          $value = $v;
        } else {
          \codename\core\app::getHook()->fire(\codename\core\hook::EVENT_TRANSLATE_TRANSLATION_KEY_MISSING, $key);
        }

        return $value;
    }

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
        $instance = null;
        try {
          $instance = new \codename\core\config\json(
            $stackname,
            $this->config['inherit'] ?? false,
            $this->config['inherit'] ?? false
          );
        } catch (\Exception $e) {
          // allow nonexisting hierarchies - but otherwise, really throw the exception.
          if($e->getCode() !== \codename\core\config\json::EXCEPTION_CONFIG_JSON_CONSTRUCT_HIERARCHY_NOT_FOUND) {
            throw $e;
          }
        }
        $this->instances[$stackname] = $instance;
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
