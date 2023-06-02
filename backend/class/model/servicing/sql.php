<?php

namespace codename\core\model\servicing;

use codename\core\model\servicing;

class sql extends servicing
{
    /**
     * [getTableIdentifier description]
     * @param \codename\core\model\schematic\sql $model [description]
     * @return string                               [description]
     */
    public function getTableIdentifier(\codename\core\model\schematic\sql $model): string
    {
        return $model->schema . '.' . $model->table;
    }

    /**
     * [getSaveUpdateSetModifiedTimestampStatement description]
     * @param \codename\core\model\schematic\sql $model [description]
     * @return string                               [description]
     */
    public function getSaveUpdateSetModifiedTimestampStatement(\codename\core\model\schematic\sql $model): string
    {
        return 'now()';
    }

    /**
     * @param $identifier
     * @return mixed
     */
    public function wrapIdentifier($identifier): mixed
    {
        return $identifier;
    }

    /**
     * @param $schema
     * @param $table
     * @return string
     */
    public function getTableIdentifierParametrized($schema, $table): string
    {
        return $schema . '.' . $table;
    }

    /**
     * @param $data
     * @return string
     */
    public function jsonEncode($data): string
    {
        return json_encode($data);
    }
}
