<?php

namespace codename\core\model\schemeless;

/**
 * [interface description]
 */
interface moduleLoaderInterface
{
    /**
     * returns a translation key for the module
     * @return string
     */
    public static function getTranslationKey(): string;
}
