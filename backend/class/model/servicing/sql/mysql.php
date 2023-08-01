<?php

namespace codename\core\model\servicing\sql;

use codename\core\model\servicing\sql;

class mysql extends sql
{
    /**
     * @param $data
     * @return string
     */
    public function jsonEncode($data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
