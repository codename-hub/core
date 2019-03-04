<?php
namespace codename\core\value\text\modelfield;

class dummy extends \codename\core\value\text\modelfield {

    /**
     * {@inheritDoc}
     * @see \codename\core\value::$validator
     */
    protected $validator = 'text';

    /**
     * creates a new text_modelfield_virtual value object
     * must be re-refined
     * @param  string $field [description]
     * @return \codename\core\value\text\modelfield
     */
    public static function getInstance(string $field) : \codename\core\value\text\modelfield {
      if(!array_key_exists($field, self::$cached)) {
        self::$cached[$field] = new self($field);
      }
      return self::$cached[$field];
    }

}
