<?php
namespace codename\core\log;

/**
 * Logging client for loggly.com
 * @package core
 * @since 2016-04-05
 */
class loggly extends \codename\core\log implements \codename\core\log\logInterface {
    
    /**
     * Contains the token that is required for authantication 
     * @var string
     */
    protected $token = null;
    
    /**
     * Contains all log instances by their file name
     * @var array of \codename\core\log
     */
    protected static $instances = array();
    
    /**
     * Constructor for the log class
     * @param array $config
     * @return \codename\core\log
     */
    protected function __construct(array $config) {
        $this->token = $config['data']['token'];
        if(array_key_exists('minlevel', $config['data'])) {
            $this->minlevel = $config['data']['minlevel'];
        }
        return $this;
    }
    
    /**
     * Returns the current instance by it's name
     * @param array $config
     * @return \codename\core\log
     */
    public static function getInstance(array $config) : \codename\core\log {
        if(!array_key_exists($config['data']['token'], self::$instances)) {
            self::$instances[$config['data']['token']] = new \codename\core\log\loggly($config);
        }
        return self::$instances[$config['data']['token']];
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\log_interface::write($text, $level)
     */
    public function write(string $text, int $level) {
        $url = 'http://logs-01.loggly.com/inputs/' . $this->token . '/tag/http/';  
        
        $data = array(
                'app' => \codename\core\app::getApp(),
                'server' => gethostname(),
                'client' => $_SERVER['REMOTE_ADDR'],
                'level' => $level,
                'text'  => $text
        );
        
        $data_string = json_encode($data);    
        
        // create CURL instance
        $ch = curl_init();
        
        // Configure CURL instance               
        curl_setopt($ch, CURLOPT_URL, $url);                                                                 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                   
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);    
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'content-type:application/x-www-form-urlencoded',                                                                             
                   'Content-Length: ' . strlen($data_string)
        ));
        $test = curl_exec($ch);
        print_r(curl_error($ch));
        curl_close($ch);
        
        return;
    }
    
}
