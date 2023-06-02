<?php

namespace codename\core\model\plugin\aggregate;

use codename\core\exception;
use codename\core\model\plugin\aggregate;

/**
 * Tell a postgreSQL model to add a calculated field to the select query
 * @package core
 * @since 2023-05-25
 */
class postgresql extends aggregate implements aggregateInterface
{
    /**
     * {@inheritDoc}
     * @param string|null $tableAlias
     * @return string
     * @throws exception
     */
    public function get(string $tableAlias = null): string
    {
        $tableAlias = $tableAlias ? $tableAlias . '.' : '';
        $field = '"' . str_replace('.', '"."', $tableAlias . $this->fieldBase->get()) . '"';
        $sql = match ($this->calculationType) {
            'count' => 'COUNT(' . $field . ')',
            'count_distinct' => 'COUNT(DISTINCT ' . $field . ')',
            'sum' => 'SUM(' . $field . ')',
            'avg' => 'AVG(' . $field . ')',
            'max' => 'MAX(' . $field . ')',
            'min' => 'MIN(' . $field . ')',
            'year' => 'date_part(\'year\', ' . $field . ')',
            'quarter' => 'date_part(\'quarter\', ' . $field . ')',
            'month' => 'date_part(\'month\', ' . $field . ')',
            'day' => 'date_part(\'day\', ' . $field . ')',
            default => throw new exception('EXCEPTION_MODEL_PLUGIN_CALCULATION_POSTGRESQL_UNKNOWN_CALCULATION_TYPE', exception::$ERRORLEVEL_ERROR, $this->calculationType),
        };
        return $sql . ' AS "' . $this->field->get() . '"';
    }
}
