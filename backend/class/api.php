<?php
namespace codename\core;

/**
 * This is the main API class. It will be used for the integration of foreign APIs, so here we will develop the clients
 * @package core
 * @since 2016-04-05
 */
class api {

    /**
     * Instance of the well-known errorstack class
     * @var \codename\core\errorstack
     */
    protected $errorstack;

    /**
     * Holds application data
     * @var \codename\core\datacontainer
     */
    protected $application;

    /**
     * @-param string $header_app
     * @-param string $header_auth
     * @-param string $header_token
     * @return \codename\core\api
     */
    public function __construct() {
        $this->errorstack = new \codename\core\errorstack($this->type);
        // $this->application = $application;
        return $this;
    }

    /**
     * Return the version string
     * @example "v1"
     * @return string
     */
    public static function getVersion() : string {
        return explode('/', $_SERVER['REQUEST_URI'])[1];
    }

    /**
     * Return the endpoint of the request
     * @example $host/v1/user/disable?...  will return the endpoint "UserDisable"
     * @return string
     */
    public static function getEndpoint() : string {
        $endpoints =  explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);
        unset($endpoints[1]);

        $ret = '';
        foreach($endpoints as $endpoint) {
            $ret .= ucfirst($endpoint);
        }
        return $ret;
    }

    /**
     * Is a helper for the printAnswer function that fills the data
     * @param unknown $data
     * @return void
     */
    protected function printSuccess($data) {
        return $this->printAnswer(array(
                'success' => 1,
                'data' => $data
            )
        );
    }

    /**
     * Is a helper for the printAnswer function that adds the 'error' key and fills it with the errorstack's content
     * @param array $data
     * @return void
     */
    protected function printError($data = array()) {
        return $this->printAnswer(array(
                'success' => 0,
                'data' => $data,
                'errors' => $this->errorstack->getErrors()
            )
        );
    }

    /**
     * Outputs the JSON answer and ends the execution
     * @param array $data
     * @return void
     */
    protected function printAnswer(array $data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
        return;
    }

}
