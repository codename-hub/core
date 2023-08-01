<?php

namespace codename\core\value\text\modelfield;

use codename\core\exception;
use codename\core\value\text\modelfield;
use ReflectionException;

class dummy extends modelfield
{
    /**
     * {@inheritDoc}
     * @see \codename\core\value::$validator
     */
    protected string $validator = 'text';

    /**
     * creates a new text_modelfield_virtual value object
     * must be re-refined
     * @param string $field [description]
     * @return modelfield
     * @throws ReflectionException
     * @throws exception
     */
    public static function getInstance(string $field): modelfield
    {
        if (!array_key_exists($field, self::$cached)) {
            self::$cached[$field] = new self($field);
        }
        return self::$cached[$field];
    }
}
