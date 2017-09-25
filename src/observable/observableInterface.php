<?php
namespace codename\core\observable;

/**
 * Observable interface
 * @package core
 * @since 2016-06-09
 */
interface observableInterface  {
    
    /**
     * Add another observer to this observable class instance
     * @param \codename\core\observer $observer
     * @return void
     */
    public function attach(\codename\core\observer $observer);
    
    /**
     * Remove an observer from this observable class instance
     * @param \codename\core\observer $observer
     * @return void
     */
    public function detach(\codename\core\observer $observer);
    
    /**
     * Poll for changes in the current class instance
     * @return void
     */
    public function notify();
    
}
