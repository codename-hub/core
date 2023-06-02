<?php

namespace codename\core\value\text\modelfield;

use codename\core\exception;
use codename\core\value\text\modelfield;
use ReflectionException;

class virtual extends modelfield
{
    /**
     * {@inheritDoc}
     * @see \codename\core\value::$validator
     */
    protected string $validator = 'text_modelfield_virtual';

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
        return self::$cached[$field] ?? self::$cached[$field] = new self($field);
    }
}
