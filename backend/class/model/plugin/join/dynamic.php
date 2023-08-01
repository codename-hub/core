<?php

namespace codename\core\model\plugin\join;

use codename\core\exception;
use codename\core\model\plugin\join;
use ReflectionException;

class dynamic extends join implements dynamicJoinInterface
{
    /**
     * [protected description]
     * @var array|null
     */
    protected ?array $__emptyDataset = null;

    /**
     * {@inheritDoc}
     * @return string
     * @throws exception
     */
    public function getJoinMethod(): string
    {
        switch ($this->type) {
            case self::TYPE_LEFT:
            case self::TYPE_INNER:
                return $this->type;
            case self::TYPE_DEFAULT:
                return self::TYPE_LEFT; // default fallback
        }
        throw new exception('EXCEPTION_MODEL_PLUGIN_JOIN_INVALID_JOIN_TYPE', exception::$ERRORLEVEL_ERROR, $this->type);
    }

    /**
     * {@inheritDoc}
     * @param array $result
     * @param array|null $params
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    public function dynamicJoin(array $result, ?array $params = null): array
    {
        $newResult = [];

        //
        // CHANGED 2020-07-22 vkey handling inside dynamic joins
        //
        $vKey = $params['vkey'];

        foreach ($result as $baseResultRow) {
            //
            // If we have a FKEY value provided, query for the dataset
            // using the given model (and all of its descendants!)
            //
            if ($leftValue = $baseResultRow[$this->modelField]) {
                //
                // TODO: we might backup the filters/filtercollections first
                // and re-apply them afterwards
                // NOTE: this might get risky, if you only apply regular filters before
                // and not default filters. It should not break the logic!
                //
                $res = $this->model->addFilter($this->referenceField, $leftValue)->search()->getResult();

                foreach ($res as $partialResultRow) {
                    //
                    // CHANGED 2020-07-22 vkey handling inside dynamic joins
                    //
                    if ($vKey) {
                        $newResult[] = array_merge(
                            $baseResultRow,
                            [
                              $vKey => $partialResultRow,
                            ]
                        );
                    } else {
                        $newResult[] = array_merge($baseResultRow, $partialResultRow);
                    }
                }
            } elseif ($this->type === static::TYPE_INNER) {
                // NONE !
            } elseif ($this->type == static::TYPE_LEFT) {
                //
                // Add a pseudo dataset (empty values)
                //
                $newResult[] = array_merge($baseResultRow, $this->getEmptyDataset());
            } else {
                // TODO: other join types?
                $newResult[] = $baseResultRow;
            }
        }

        // Resetting the cache for producing an empty dataset
        $this->resetEmptyDataset();

        return $newResult;
    }

    /**
     * Returns an empty dataset using the current model configuration
     * @return array [description]
     */
    protected function getEmptyDataset(): array
    {
        if (!$this->__emptyDataset) {
            $this->__emptyDataset = [];
            foreach ($this->model->getCurrentAliasedFieldlist() as $field) {
                $this->__emptyDataset[$field] = null;
            }
        }
        return $this->__emptyDataset;
    }

    /**
     * [resetEmptyDataset description]
     * @return void
     */
    protected function resetEmptyDataset(): void
    {
        $this->__emptyDataset = null;
    }
}
