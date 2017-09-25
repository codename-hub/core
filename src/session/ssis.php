<?php
namespace codename\core\session;

/**
 * Storing and checking SSIS sessions
 * @package core
 * @since 2016-05-03
 */
class ssis extends \codename\core\session implements \codename\core\session\sessionInterface {

    /**
     * Contains the API Instance
     * @var \codename\core\api_codename_ssis
     */
    protected $apiInst = null;
    
    /**
     * Contains the configuration for the API client
     * @var array
     */
    protected $config = array();
    
    /**
     * 
     * @param array $data
     */
    public function __CONSTRUCT(array $data) {
        if(count($errors = app::getValidator('structure_api_codename')->validate($data)) > 0) {
            print_r($errors);
            return false;
        }
        
        $this->config = $data;
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::start($data)
     */
    public function start(array $data) : \codename\core\session {
        $data['session_data'] = serialize($data['session_data']);
        $this->myModel()->save($data);
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::destroy()
     */
    public function destroy() {
        $sess = $this->myModel()->addFilter('session_sessionid', $_COOKIE['PHPSESSID'])->search()->getResult();
        if(count($sess) == 0) {
            return;
        }
        foreach($sess as $session) {
            $this->myModel()->delete($session['session_id']);
        }
        setcookie ("PHPSESSID", "", time() - 3600);
        return;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::getData($key)
     */
    public function getData(string $key='') {
        if(strlen($key) == 0) {
            return $this->data;
        }
        if(!$this->isDefined($key)) {
            return null;
        }
        return $this->data[$key];
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::setData($key, $value)
     */
    public function setData(string $key, $value) {
        $this->data[$key] = $value;
        return;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\session_interface::isDefined($key)
     */
    public function isDefined(string $key) : bool {
        return array_key_exists($key, $this->data);
    }
    
    /**
     * 
     */
    public function identify() : bool {
        $data = $this->myModel()->loadByUnique('session_sessionid', $_COOKIE['PHPSESSID']);
        if(count($data) > 0) {
            $sessData = unserialize($data['session_data']);
            if(is_array($sessData) && count($sessData) > 0) {
                $this->data = array_merge($data, unserialize($data['session_data']));
            }
            return true;
        }
        
        $this->getApi()->getLoginurl($this->getRequest());
        
        return false;
    }
    
    /**
     * @todo DOCUMENTATION
     */
    protected function myModel() : model {
        return $this->getModel('session', 'core');
    }
    
    /**
     * @todo DOCUMENTATION
     */
    protected function getApi() : \codename\core\api_codename_ssis {
        if(is_null($this->apiInst)){
            $this->apiInst = new \codename\core\api_codename_ssis($this->config);
        }
        return $this->apiInst;
    }
    
}
