<?php
namespace codename\core\translate;

interface translateInterface {
    
    /**
     * Gets the translation from the file. $key contains two sections separated by a period (.) Before the period is the file name, after it the key.
     * @param string $key
     */
    public function translate(string $key, array $data = array()) : string;
    
}
