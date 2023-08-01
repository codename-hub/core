<?php

namespace codename\core\model\plugin\aggregate;

use codename\core\exception;
use codename\core\model\plugin\aggregate;

/**
 * Tell a MySQL model to add a calculated field to the select query
 * @package core
 * @since 2017-05-18
 */
class mysql extends aggregate implements aggregateInterface
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
        $sql = match ($this->calculationType) {
            'count' => 'COUNT(' . $tableAlias . $this->fieldBase->get() . ')',
            'count_distinct' => 'COUNT(DISTINCT ' . $tableAlias . $this->fieldBase->get() . ')',
            'sum' => 'SUM(' . $tableAlias . $this->fieldBase->get() . ')',
            'avg' => 'AVG(' . $tableAlias . $this->fieldBase->get() . ')',
            'max' => 'MAX(' . $tableAlias . $this->fieldBase->get() . ')',
            'min' => 'MIN(' . $tableAlias . $this->fieldBase->get() . ')',
            'year' => 'YEAR(' . $tableAlias . $this->fieldBase->get() . ')',
            'quarter' => 'QUARTER(' . $tableAlias . $this->fieldBase->get() . ')',
            'month' => 'MONTH(' . $tableAlias . $this->fieldBase->get() . ')',
            'day' => 'DAY(' . $tableAlias . $this->fieldBase->get() . ')',
            'timestampdiff-year' => 'TIMESTAMPDIFF(YEAR, ' . $tableAlias . $this->fieldBase->get() . ', CURDATE())',
            default => throw new exception('EXCEPTION_MODEL_PLUGIN_CALCULATION_MYSQL_UNKNOWN_CALCULATION_TYPE', exception::$ERRORLEVEL_ERROR, $this->calculationType),
        };
        return $sql . ' AS ' . $this->field->get();
    }
}
