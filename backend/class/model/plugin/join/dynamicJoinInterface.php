<?php

namespace codename\core\model\plugin\join;

/**
 * [interface description]
 * @var [type]
 */
interface dynamicJoinInterface
{
    /**
     * [performDynamicJoin description]
     * @param array $result [base result before dynamic join/query]
     * @param array|null $params [internal parameter handling]
     * @return array                      [modified result]
     */
    public function dynamicJoin(array $result, ?array $params = null): array;
}
