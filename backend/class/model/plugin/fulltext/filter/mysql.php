<?php

namespace codename\core\model\plugin\fulltext\filter;

use codename\core\exception;
use codename\core\model\plugin\managedFilterInterface;
use codename\core\value\text\modelfield;
use ReflectionException;

/**
 * [mysql description]
 */
class mysql implements managedFilterInterface
{
    /**
     * $fields that are used to filter data from the model
     * @var modelfield[]
     */
    public ?array $fields = null;

    /**
     * Contains the value to searched in the $field
     * @var mixed
     */
    public mixed $value = null;

    /**
     * Contains the $operator for the $field
     * @var string $operator
     */
    public string $operator = "=";

    /**
     * the conjunction to be used (AND, OR, XOR, ...)
     * may be null
     * @var null|string $conjunction
     */
    public ?string $conjunction = null;

    /**
     * [__construct description]
     * @param modelfield|string[] $fields [description]
     * @param null $value
     * @param string|null $conjunction [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(array $fields, $value = null, string $conjunction = null)
    {
        foreach ($fields as &$thisfield) {
            if (!$thisfield instanceof modelfield) {
                $thisfield = modelfield::getInstance($thisfield);
            }
        }
        $this->fields = $fields;
        $this->value = $value;
        $this->operator = '>'; // by default, this value, and nothing else.
        $this->conjunction = $conjunction;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilterQueryParameters(): array
    {
        return [
          'match_against' => $this->value,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getFilterQuery(array $variableNameMap, $tableAlias = null): string
    {
        $tableAlias = $tableAlias ? $tableAlias . '.' : '';
        $fields = [];
        foreach ($this->fields as $field) {
            $fields[] = $tableAlias . $field->get();
        }
        return 'MATCH (' . implode(', ', $fields) . ') AGAINST (:' . $variableNameMap['match_against'] . ' IN BOOLEAN MODE) ' . $this->operator . ' 0';
    }
}
