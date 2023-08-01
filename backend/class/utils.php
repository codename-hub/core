<?php

namespace codename\core;

/**
 * This is the utils class. It provides several utility methods.
 * @package core
 * @since 2016-10-07
 */
class utils
{
    /**
     * Merges/unifies two arrays recursively
     * @see http://stackoverflow.com/questions/25712099/php-multidimensional-array-merge-recursive
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function array_merge_recursive_ex(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::array_merge_recursive_ex($merged[$key], $value);
            } elseif (is_numeric($key)) {
                if (!in_array($value, $merged)) {
                    $merged[] = $value;
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
