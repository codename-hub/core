<?php

namespace codename\core\model\plugin\join;

use codename\core\model;
use codename\core\model\plugin\filter;
use codename\core\model\plugin\join;

abstract class recursive extends join
{
    /**
     * Field that is used for self-reference
     * selfReferenceField => anchorField
     * @var null|string
     */
    protected ?string $selfReferenceField = null;

    /**
     * Field that is used as anchor point
     * @var null|string
     */
    protected ?string $anchorField = null;

    /**
     * [protected description]
     * @var filter[]
     */
    protected array $anchorConditions = [];

    /**
     * {@inheritDoc}
     */
    public function __construct(
        model $model,
        string $selfReferenceField,
        string $anchorField,
        array $anchorConditions,
        string $type,
        $modelField,
        $referenceField,
        array $conditions = [],
        ?string $virtualField = null
    ) {
        parent::__construct($model, $type, $modelField, $referenceField, $conditions, $virtualField);
        $this->selfReferenceField = $selfReferenceField;
        $this->anchorField = $anchorField;
        if (count($anchorConditions) > 0) {
            foreach ($anchorConditions as $cond) {
                if ($cond instanceof filter) {
                    $this->anchorConditions[] = $cond;
                } else {
                    $this->anchorConditions[] = $this->createFilterPluginInstance($cond);
                }
            }
        } else {
            // throw new exception('PLUGIN_JOIN_RECURSIVE_ANCHOR_CONDITIONS_REQUIRED', exception::$ERRORLEVEL_ERROR);
        }
    }

    /**
     * [createFilterPluginInstance description]
     * @param array $data [description]
     * @return filter        [description]
     */
    abstract protected function createFilterPluginInstance(array $data): filter;
}
