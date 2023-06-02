<?php

namespace codename\core\model\plugin\aggregate;

use codename\core\exception;
use codename\core\model\plugin\aggregate;

/**
 * Tell a SQLite model to add a calculated field to the select query
 * @package core
 * @since 2021-03-22
 */
class sqlite extends aggregate implements aggregateInterface
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
            'year' => 'CAST(strftime(\'%Y\',' . $tableAlias . $this->fieldBase->get() . ') as integer)',
            'quarter' => '(CAST(strftime(\'%m\',' . $tableAlias . $this->fieldBase->get() . ') as integer) + 2) / 3',
            'month' => 'CAST(strftime(\'%m\',' . $tableAlias . $this->fieldBase->get() . ') as integer)',
            'day' => 'CAST(strftime(\'%d\',' . $tableAlias . $this->fieldBase->get() . ') as integer)',
            default => throw new exception('EXCEPTION_MODEL_PLUGIN_CALCULATION_SQLITE_UNKNOWN_CALCULATION_TYPE', exception::$ERRORLEVEL_ERROR, $this->calculationType),
        };
        return $sql . ' AS ' . $this->field->get();
    }
}
