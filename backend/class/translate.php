<?php
namespace codename\core;

/**
 * Utilizing translations that are requested by keys
 * @package core
 * @since 2016-02-12
 */
abstract class translate implements \codename\core\translate\translateInterface {

    /**
     * client configuration
     * @var array
     */
    protected $config;

    /**
     *
     */
    public function __construct($config = array())
    {
      $this->config = $config;
    }

    /**
     * [getPrefix description]
     * @return string [description]
     */
    public function getPrefix() : string {
      return '';
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\translate_interface::translate($key, $data)
     */
    public function translate(string $key, array $data = array()) : string {
        $cacheGroup = 'TRANSLATION_' . app::getApp() . '_' . $this->getPrefix() . '_';
        $text = $this->cachedTranslations[$cacheGroup.$key] ?? null;

        if($text) {
          return $text;
        } else {
          $text = app::getCache()->get($cacheGroup, $key);
          if($text) {
            $this->cachedTranslations[$cacheGroup.$key] = $text;
          }
        }

        if(strlen($text) > 0) {
            return $text;
        }

        app::getCache()->set($cacheGroup, $key, $text = $this->getTranslation($key));
        $this->cachedTranslations[$cacheGroup.$key] = $text;
        return $text;
    }

    /**
     * [protected description]
     * @var string[]
     */
    protected $cachedTranslations = [];

}
