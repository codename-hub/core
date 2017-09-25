<?php
namespace codename\core\worker;

interface workerInterface {
    
    /**
     * I start the worker
     * @return void
     */
    public function start(string $class);
    
    /**
     * I stop the worker
     * @return void
     */
    public function stop();
    
    /**
     * I pause the worker
     * @return void
     */
    public function pause();
    
    /**
     * I resume the worker
     * @return void
     */
    public function resume();
    
    /**
     * I skip the current queue entry 
     * @return void
     */
    public function skip();
    
    /**
     * I work the given entry
     * @return void
     */
    public function work(array $queue);
    
}
