<?php
namespace codename\core\queue;
use \codename\core\app;

/**
 * I help you storing the queue tasks on a postgreSQL table
 * @package core
 * @since 2016-06-14
 */
class postgresql extends \codename\core\queue implements \codename\core\queue\queueInterface {

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\queue_interface::add($class, $method, $identifier, $actions)
     */
    public function add(string $class, string $method, string $identifier, array $actions) {
        app::getModel('queue')->save(array(
                'queue_class' => $class,
                'queue_method' => $method,
                'queue_identifier' => $identifier,
                'queue_data' => json_encode($actions),
                'queue_flag' => 0
        ));
        return;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\queue_interface::load($class, $identifier)
     */
    public function load(string $class, string $identifier = '') {
        $model = app::getModel('queue');
        $model->addFilter('queue_class', $class)->setLimit(1);
        
        if(strlen($identifier) > 0) {
            $model->addFilter('queue_identifier', $identifier);
        }
        
        $data = $model->addFilter('queue_flag', 0)->search()->getResult();
        
        if(count($data) == 0) {
            return null;
        }
        return $data[0];
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\queue_interface::remove($entry)
     */
    public function remove(string $id) {
        app::getModel('queue')->delete($id);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\queue_interface::lock($class, $identifier)
     */
    public function lock(string $class, string $identifier) {
        $data = $this->load($class, $identifier);
        if(count($data) == 0) {
            return;
        }
        $data['queue_flag'] = 1;
        app::getModel('queue')->save($data);
        return;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\queue_interface::unlock($class, $identifier)
     */
    public function unlock(string $class, string $identifier) {
        $data = $this->load($class, $identifier);
        if(count($data) == 0) {
            return;
        }
        $data = $data[0];
        $data['queue_flag'] = 0;
        app::getModel('queue')->save($data);
        return;
        
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\queue_interface::list($class)
     */
    public function listElements(string $class = '') : array {
        return app::getModel('queue')->addFilter('queue_class', $class)->search()->getResult();
    }
    
}
