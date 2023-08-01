<?php

namespace codename\core;

use codename\core\translate\translateInterface;
use ReflectionException;

/**
 * Utilizing translations that are requested by keys
 * @package core
 * @since 2016-02-12
 */
abstract class translate implements translateInterface
{
    /**
     * client configuration
     * @var mixed
     */
    protected mixed $config;
    /**
     * [protected description]
     * @var string[]
     */
    protected array $cachedTranslations = [];

    /**
     * @param mixed $config
     */
    public function __construct(mixed $config = [])
    {
        $this->config = $config;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $key
     * @param array $data
     * @return string
     * @throws ReflectionException
     * @throws exception
     * @see translate_interface::translate, $data)
     */
    public function translate(string $key, array $data = []): string
    {
        $cacheGroup = 'TRANSLATION_' . app::getApp() . '_' . $this->getPrefix() . '_';
        $text = $this->cachedTranslations[$cacheGroup . $key] ?? null;

        if ($text) {
            return $text;
        } else {
            $text = app::getCache()->get($cacheGroup, $key);
            if ($text) {
                $this->cachedTranslations[$cacheGroup . $key] = $text;
            }
        }

        if (strlen($text) > 0) {
            return $text;
        }

        app::getCache()->set($cacheGroup, $key, $text = $this->getTranslation($key));
        $this->cachedTranslations[$cacheGroup . $key] = $text;
        return $text;
    }

    /**
     * [getPrefix description]
     * @return string [description]
     */
    public function getPrefix(): string
    {
        return '';
    }
}
