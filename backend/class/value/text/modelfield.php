<?php

namespace codename\core\value\text;

use codename\core\exception;
use codename\core\value\text;
use ReflectionException;

class modelfield extends text
{
    /**
     * @var modelfield[]
     */
    protected static array $cached = [];
    /**
     * {@inheritDoc}
     * @see \codename\core\value::$validator
     */
    protected string $validator = 'text_modelfield';
    /**
     * @var string|null
     */
    protected ?string $field = null;
    /**
     * @var string|null
     */
    protected ?string $table = null;
    /**
     * @var string|null
     */
    protected ?string $schema = null;

    /**
     * {@inheritDoc}
     */
    public function __construct($value)
    {
        parent::__construct($value);
        $exp = explode('.', $value);
        if (count($exp) === 1) {
            $this->field = $exp[0];
        } elseif (count($exp) === 2) {
            $this->table = $exp[0];
            $this->field = $exp[1];
        } elseif (count($exp) === 3) {
            $this->schema = $exp[0];
            $this->table = $exp[1];
            $this->field = $exp[2];
        } else {
            // throw exception
        }
        return $this;
    }

    /**
     * creates a new text_modelfield_virtual value object
     * @param string $field [description]
     * @return modelfield          [description]
     * @throws ReflectionException
     * @throws exception
     */
    public static function getInstance(string $field): modelfield
    {
        return self::$cached[$field] ?? self::$cached[$field] = new self($field);
    }

    /**
     * {@inheritDoc}
     */
    public function get(): mixed
    {
        return $this->field;
    }

    /**
     * @return string|null
     */
    public function getTable(): ?string
    {
        return $this->table;
    }

    /**
     * @return string|null
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}
