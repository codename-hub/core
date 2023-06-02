<?php

namespace codename\core\model\servicing\sql;

use codename\core\model\servicing\sql;

class postgresql extends sql
{

    /**
     * {@inheritDoc}
     */
    public function getTableIdentifier(\codename\core\model\schematic\sql $model): string
    {
        //
        // SQLite doesn't support schema.table syntax, as there's only one database
        // therefore, we 'fake' it by using `schema.table`
        //
        return '"' . $model->schema . '"."' . $model->table . '"';
    }

    /**
     * @param $schema
     * @param $table
     * @return string
     */
    public function getTableIdentifierParametrized($schema, $table): string
    {
        return '"' . $schema . '"."' . $table . '"';
    }

    /**
     * @param $identifier
     * @return string
     */
    public function wrapIdentifier($identifier): string
    {
        return '"' . $identifier . '"';
    }
}
