<?php

namespace codename\core\model\plugin;

use codename\core\model;
use codename\core\model\plugin;

/**
 * Provide model joining as a plugin
 * @package core
 * @since 2017-11-28
 */
abstract class join extends plugin
{
    /**
     * use current model default
     * @var string
     */
    public const TYPE_DEFAULT = 'DEFAULT';

    /**
     * perform a left join
     * @var string
     */
    public const TYPE_LEFT = 'LEFT';

    /**
     * perform a right join
     * @var string
     */
    public const TYPE_RIGHT = 'RIGHT';

    /**
     * perform a full join
     * @var string
     */
    public const TYPE_FULL = 'FULL';

    /**
     * perform an inner join
     * @var string
     */
    public const TYPE_INNER = 'INNER';

    /**
     * $model used for joining
     * @var model
     */
    public $model = null;

    /**
     * Contains the join type
     * @var null|string $type
     */
    public ?string $type = null;

    /**
     * Contains the field to be joined upon (reference - the OTHER model)
     * @var null|string|array
     */
    public null|string|array $referenceField = null;

    /**
     * Contains the field to be joined upon (this model)
     * @var null|string|array
     */
    public null|string|array $modelField = null;

    /**
     * [public description]
     * @var array
     */
    public array $conditions = [];

    /**
     * the current alias that is used
     * @var string|null
     */
    public ?string $currentAlias = null;

    /**
     * [public description]
     * @var string|null
     */
    public ?string $virtualField = null;

    /**
     * @see \codename\core\model_plugin_filter::__construct(string $field, string $value, string $operator)
     */
    public function __construct(model $model, string $type, null|string|array $modelField, null|string|array $referenceField, array $conditions = [], ?string $virtualField = null)
    {
        $this->model = $model;
        $this->type = $type;
        $this->referenceField = $referenceField;
        $this->modelField = $modelField;
        $this->conditions = $conditions;
        $this->virtualField = $virtualField;
        // TODO: perform null check?
        return $this;
    }

    /**
     * provides information about this join plugin's parameters
     * to be used for model caching features
     * @return array
     */
    public function getCurrentCacheIdentifierParameters(): array
    {
        return [
          'method' => $this->getJoinMethod(),
          'modelField' => $this->modelField,
          'referenceField' => $this->referenceField,
          'conditions' => $this->conditions,
          'vfield' => $this->virtualField,
        ];
    }

    /**
     * return the database-driver-specific keyword for this join
     * @return string
     */
    abstract public function getJoinMethod(): string;
}
