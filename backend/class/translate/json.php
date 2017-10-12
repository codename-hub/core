<?php
namespace codename\core\translate;

use \codename\core\app;

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
     * @todo DOCUMENTATION
     */
    protected function getTranslation(string $key) : string {
        $keystr = $key;

        // Split into maximum of 2 elements (dots may exist afterwards)
        $key = explode('.', $key, 2);

        if(count($key) != 2) {
            return 'KEYLENGTH NOT MATCHING (' . $keystr . ')';
        }

        $key[0] = strtolower($key[0]);
        $key[1] = strtoupper($key[1]);

        $stackname = 'translation/' . app::getInstance('request')->getData('lang') . '/' . $key[0] . '.json';

        /**
         * [$instance description]
         * @var \codename\core\config\json
         */
        $instance = null;

        if(array_key_exists($stackname, $this->instances)) {
          $instance = $this->instances[$stackname];
        } else {
          $instance = new \codename\core\config\json(
            $stackname,
            $this->config['inherit'] ?? false,
            $this->config['inherit'] ?? false
          );
        }

        $keyname = $key[1];
        $value = $keyname;

        $v = $instance->get($key[1]);
        if($v != null) {
          $value = $v;
        } else {
          \codename\core\app::getHook()->fire(\codename\core\hook::EVENT_TRANSLATE_TRANSLATION_KEY_MISSING, $key);
        }

        return $value;
    }

}
