<?php

namespace codename\core\queue;

interface queueInterface
{
    /**
     * I will create a new entry in the queue list
     * The <b>$class</b> argument defines what queue worker must be used
     * The <b>$method</b> argument defines the name of the callable method in the worker
     * The <b>$identifier</b> is an identifier for the worker (e.g. when it is a model, this is the primary key
     * The <b>$actions</b> is an array of things the worker will do (e.g. overwrite fields)
     * @param string $class
     * @param string $method
     * @param string $identifier
     * @param array $actions
     * @return void
     */
    public function add(string $class, string $method, string $identifier, array $actions): void;

    /**
     * I will load an entry from a queue list.
     * The <b>$class</b> argument defines what queue the object will be loaded for
     * The <b>$identifier</b> argument is optional. You might want to load one specific entry from the queue here
     * @param string $class
     * @param string $identifier
     * @return mixed
     */
    public function load(string $class, string $identifier = ''): mixed;

    /**
     * I will remove an entry from a queue list
     * The <b>$id</b> argument defines what element to delete
     * @param string $id
     * @return void
     */
    public function remove(string $id): void;

    /**
     * I will lock one object in the queue list, so that it will be ignored by the 'load' method
     * The <b>$class</b> argument is the queue the object is located in
     * The <b>$identifier</b> argument defines the specific element in the queue
     * @param string $class
     * @param string $identifier
     * @return void
     */
    public function lock(string $class, string $identifier): void;

    /**
     * I will remove the lock status from the given object on the queue list
     * The <b>$class</b> argument is the queue the object is located in
     * The <b>$identifier</b> argument defines the specific element in the queue
     * @param string $class
     * @param string $identifier
     * @return void
     */
    public function unlock(string $class, string $identifier): void;

    /**
     * I will list all the queue entries.
     * The <b>$class</b> argument can be added to the method if you want a list of objects in this queue
     * @param string $class
     * @return array
     */
    public function listElements(string $class = ''): array;
}
