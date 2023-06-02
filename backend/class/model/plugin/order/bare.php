<?php

namespace codename\core\model\plugin\order;

use codename\core\model\plugin\order;

/**
 * Bare ordering plugin
 * @package core
 * @since 2017-12-05
 */
class bare extends order implements orderInterface, executableOrderInterface
{
    /**
     * {@inheritDoc}
     */
    public function order(array $data): array
    {
        $key = $this->field->get();
        self::stable_usort($data, function (array $left, array $right) use ($key) {
            if ($left[$key] == $right[$key]) {
                return 0;
            }
            $prepSort = [$left[$key], $right[$key]];
            sort($prepSort);
            if ($prepSort[0] === $left[$key]) {
                return $this->direction == 'ASC' ? -1 : 1;
            } else {
                return $this->direction == 'ASC' ? 1 : -1;
            }
        });
        return $data;
    }

    /**
     * stable usort function
     * @var [type]
     */
    protected static function stable_usort(array &$array, $value_compare_func): bool
    {
        $index = 0;
        foreach ($array as &$item) {
            $item = [$index++, $item];
        }
        $result = usort($array, function ($a, $b) use ($value_compare_func) {
            $result = call_user_func($value_compare_func, $a[1], $b[1]);
            return $result == 0 ? $a[0] - $b[0] : $result;
        });
        foreach ($array as &$item) {
            $item = $item[1];
        }
        return $result;
    }
}
