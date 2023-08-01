<?php

namespace codename\core\worker;

use codename\core\app;
use codename\core\exception;
use ReflectionException;

/**
 * CRUD worker class.
 * Made for bulk deleting, updating entries in a model
 * @package core
 * @since 2016-06-14
 */
class crud
{
    /**
     * Update the fields passed in $queueentry['data'] of the model entry identified by $queueentry['identifier']
     * @param array $queueentry
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function bulk_update(array $queueentry): void
    {
        if (!array_key_exists('identifier', $queueentry)) {
            echo 'No identifier';
            return;
        }
        if (!array_key_exists('data', $queueentry)) {
            echo 'No data-object';
            return;
        }
        $identifier = explode(':', $queueentry['identifier']);
        if (count($identifier) != 2) {
            echo 'Invalid Identifier for this action';
            return;
        }

        $data = app::getModel($identifier[0])->load($identifier[1]);

        foreach ($queueentry['data'] as $key => $value) {
            $data[$key] = $value;
        }

        app::getModel($identifier[0])->save($data);
    }
}
