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
     *
     * {@inheritDoc}
     * @see \codename\core\translate_interface::translate($key, $data)
     */
    public function translate(string $key, array $data = array()) : string {
        $text = app::getCache()->get('TRANSLATION_' . app::getApp() . '_', $key);
        if(strlen($text) > 0) {
            return $text;
        }
        app::getCache()->set('TRANSLATION_' . app::getApp() . '_', $key, $text = $this->getTranslation($key));
        return $text;
    }

}
