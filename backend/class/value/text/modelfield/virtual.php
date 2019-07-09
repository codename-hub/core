<?php
namespace codename\core\value\text\modelfield;

class virtual extends \codename\core\value\text\modelfield {

    /**
     * {@inheritDoc}
     * @see \codename\core\value::$validator
     */
    protected $validator = 'text_modelfield_virtual';

    /**
     * creates a new text_modelfield_virtual value object
     * must be re-refined
     * @param  string $field [description]
     * @return \codename\core\value\text\modelfield
     */
    public static function getInstance(string $field) : \codename\core\value\text\modelfield {
      return self::$cached[$field] ?? self::$cached[$field] = new self($field);
    }

}
