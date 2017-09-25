<?php
namespace codename\core\worker;
use \codename\core\app;

/**
 * CRUD worker class.
 * <br />Made for bulk deleting, updating entries in a model
 * @package core
 * @since 2016-06-14
 */
class crud {

    /**
     * Update the fields passed in $queueentry['data'] of the model entry identified by $queueentry['identifier']
     * @param array $queueentry
     * @return void
     */
    public function bulk_update(array $queueentry) {
        if(!array_key_exists('identifier', $queueentry)) {
            echo 'No identifier';
            return;
        }
        if(!array_key_exists('data', $queueentry)) {
            echo 'No data-object';
            return;
        }
        $identifier = explode(':', $queueentry['identifier']);
        if(count($identifier) != 2) {
            echo 'Invalid Identifier for this action';
            return;
        }

        $data = app::getModel($identifier[0])->load($identifier[1]);

        foreach($queueentry['data'] as $key => $value) {
            $data[$key] = $value;
        }

        app::getModel($identifier[0])->save($data);
        return;
    }

}
