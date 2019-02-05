<?php
namespace codename\core\translate;

/**
 * interface for translation clients/modules
 */
interface translateInterface {

    /**
     * Gets the translation from the file.
     * $key contains two sections separated by a period (.) Before the period is the file name, after it the key.
     * you may supply additional parameters in $data, if translated value contains variable markers
     * @param  string $key  [description]
     * @param  array  $data [description]
     * @return string       [description]
     */
    public function translate(string $key, array $data = []) : string;

    /**
     * returns all translations using a given prefix
     * @param  string $prefix   [prefix]
     * @return array|null       [translations]
     */
    public function getAllTranslations(string $prefix) : ?array;

}
