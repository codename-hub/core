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
            
        $file = app::getInheritedPath('translation/' . app::getInstance('request')->getData('lang') . '/' . $key[0] . '.json');
    
        if(!app::getInstance('filesystem_local')->fileAvailable($file)) {
            throw new \codename\core\exception(self::EXCEPTION_GETTRANSLATION_TRANSLATIONFILEMISSING, \codename\core\exception::$ERRORLEVEL_ERROR, $file);
        }
    
        $file = file_get_contents($file);
    
        $file = json_decode($file);
    
        if(is_null($file)) {
            throw new \codename\core\exception(self::EXCEPTION_GETTRANSLATION_TRANSLATIONFILEINVALID, \codename\core\exception::$ERRORLEVEL_FATAL, $file);
        }
    
        $keyname = $key[1];
        $value = $keyname;
        if(array_key_exists($key[1], $file)) {
            $value = $file->$keyname;
        } else {
            \codename\core\app::getHook()->fire(\codename\core\hook::EVENT_TRANSLATE_TRANSLATION_KEY_MISSING, $key);
        }
    
        return $value;
    }
    
}
