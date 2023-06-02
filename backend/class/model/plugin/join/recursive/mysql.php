<?php

namespace codename\core\model\plugin\join\recursive;

use codename\core\exception;
use codename\core\model\plugin\filter;
use codename\core\value\text\modelfield;
use ReflectionException;

class mysql extends sql
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
                throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_RECURSIVE_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
            case self::TYPE_INNER:
                return 'INNER JOIN';
        }
        throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_RECURSIVE_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
    }

    /**
     * {@inheritDoc}
     * @param array $data
     * @return filter
     * @throws ReflectionException
     * @throws exception
     */
    protected function createFilterPluginInstance(
        array $data
    ): filter {
        return new filter\mysql(
            modelfield::getInstance($data['field']),
            $data['value'],
            $data['operator'],
            $data['conjunction'] ?? null, // ??
        );
    }
}
