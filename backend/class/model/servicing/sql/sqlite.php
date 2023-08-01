<?php

namespace codename\core\model\servicing\sql;

use codename\core\model\schematic\sql;

class sqlite extends \codename\core\model\servicing\sql
{
    /**
     * {@inheritDoc}
     */
    public function getTableIdentifier(sql $model): string
    {
        //
        // SQLite doesn't support schema.table syntax, as there's only one database
        // therefore, we 'fake' it by using `schema.table`
        //
        return '`' . $model->schema . '.' . $model->table . '`';
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveUpdateSetModifiedTimestampStatement(sql $model): string
    {
        //
        // SQLite's implementation differs from other SQL databases
        //
        return 'datetime(\'now\')';
    }

    /**
     * @param $schema
     * @param $table
     * @return string
     */
    public function getTableIdentifierParametrized($schema, $table): string
    {
        return '`' . $schema . '.' . $table . '`';
    }

    /**
     * @param $data
     * @return string
     */
    public function jsonEncode($data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
