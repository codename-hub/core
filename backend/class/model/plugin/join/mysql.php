<?php

namespace codename\core\model\plugin\join;

use codename\core\exception;
use codename\core\model\plugin\join;

/**
 * Provide model joining as a plugin
 * MySQL Implementation
 * @package core
 * @since 2017-11-28
 */
class mysql extends join
{
    /**
     * {@inheritDoc}
     * @return string
     * @throws exception
     */
    public function getJoinMethod(): string
    {
        switch ($this->type) {
            case self::TYPE_DEFAULT:
            case self::TYPE_LEFT:
                return 'LEFT JOIN';
            case self::TYPE_RIGHT:
                return 'RIGHT JOIN';
            case self::TYPE_FULL:
                // not supported on MySQL
                throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_MYSQL_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
            case self::TYPE_INNER:
                return 'INNER JOIN';
        }
        throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
    }
}
