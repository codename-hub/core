<?php

namespace codename\core\model;

use codename\core\model;

/**
 * defines an interface for handling cross-model
 * virtual nested fields
 */
interface virtualFieldResultInterface
{
    /**
     * creates/fills virtual fields with their respective data, on need
     * should only be used internally
     *
     * @param array $result [input result]
     * @param array $track [temporary/internal tracking array for nesting level calculations]
     * @return array          [modified result]
     */
    public function getVirtualFieldResult(array $result, array &$track = []): array;

    /**
     * changes the state of the virtual field result handling
     *
     * @param bool $state [state of the virtual field result handling]
     * @return model [current instance]
     */
    public function setVirtualFieldResult(bool $state): model;
}
