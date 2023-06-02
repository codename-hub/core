<?php

namespace codename\core\model\plugin\join;

use codename\core\exception;
use codename\core\model;
use codename\core\model\plugin\join;

/**
 * Provide model joining as a plugin
 * PostgreSQL Implementation
 * @package core
 * @since 2020-11-20
 */
class postgresql extends join
{
    /**
     * {@inheritDoc}
     */
    public function __construct(
        model $model,
        string $type,
        $modelField,
        $referenceField,
        array $conditions = [],
        ?string $virtualField = null
    ) {
        parent::__construct($model, $type, '"' . $modelField . '"', '"' . $referenceField . '"', $conditions, $virtualField);
    }

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
                // not implemented right now?
                throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_POSTGRESQL_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
            case self::TYPE_INNER:
                return 'INNER JOIN';
        }
        throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
    }
}
