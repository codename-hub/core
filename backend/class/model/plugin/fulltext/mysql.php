<?php

namespace codename\core\model\plugin\fulltext;

use codename\core\model\plugin\fulltext;

/**
 * Tell a MySQL model to add a calculated field to the select query
 * @package core
 * @author Ralf Thieme
 * @since 2019-03-04
 */
class mysql extends fulltext implements fulltextInterface
{
    /**
     * {@inheritDoc}
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function getField(): string
    {
        return $this->field->get();
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $value, string $tableAlias = null): string
    {
        $tableAlias = $tableAlias ? $tableAlias . '.' : '';
        $fields = [];
        foreach ($this->fields as $field) {
            $fields[] = $tableAlias . $field->get();
        }
        $sql = 'MATCH (' . implode(', ', $fields) . ') AGAINST (:' . $value . ' IN BOOLEAN MODE)';
        $alias = $this->field->get();
        if ($alias ?? false) {
            $sql .= ' AS ' . $alias;
        }
        return $sql;
    }
}
