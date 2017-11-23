<?php
namespace codename\core\api;

use \codename\core\app;

/**
 * Extension for \codename\core\api representing the clients for our own API Service Providers
 * <br />Also @see \codename\core\context\api for our own API Service provider's structure
 * @package core
 * @since 2016-04-05
 */
class codename extends \codename\core\api {

    /**
     * Contains the application data
     * @var \codename\core\datacontainer
     */
    protected $authentication;

    /**
     * Contains configuration of the service provider (host, port, etc)
     * @var \codename\core\value\structure\api\codename\serviceprovider
     */
    protected $serviceprovider;

    /**
     * Contains an instance of Errorstack to provide deep information in case of errors
     * @var \codename\core\errorstack
     */
    protected $errorstack;

    /**
     * Contains the CURL Handler for the next request
     * <br />Is used to handle HTTP(s) requests for retrieving and sending data from the foreign service
     * @var \curl
     */
    protected $curlHandler;

    /**
     * What is the request's special secret string?
     * <br />Many codename API services are relying on a second authentication factor.
     * <br />By definition the second factor is dependent from the concret topic of the request
     * <br />The given salt is filled with the requested key's name.
     * <br />So every different key has a different salt.
     * @internal Will not be transferred unencrypted
     * @var string
     */
    protected $salt = '';

    /**
     * Contains the API serive provider's response to the given request
     * <br />After retrieving a response from the foreign host, it will be stored here.
     * @var array
     */
    protected $response = '';

    /**
     * Contains the API type.
     * <br />Typically is defined using the name of the foreign service in upper-case characters
     * @example YOURAPITYPE
     * @var string
     */
    protected $type = '';

    /**
     * Contains POST data for the request
     * <br />Typically all headers for authentication and data retrieval
     * @var array
     */
    protected $data = array();

    /**
     * Contains a list of fields that must not be sent via the POST request
     * <br />Most of the given fields may irritate the foreign service as they are based on the core core
     * @internal This is since these fields are request arguments responsible for app routing.
     * @var array
     */
    public $forbiddenpostfields = array('app', 'context', 'view', 'action', 'callback', 'template', 'lang');

    /**
     * Create instance
     * @param array $data
     * @return \codename\core\api_codename
     */
    public function __CONSTRUCT(array $data) {
        $this->errorstack = new \codename\core\errorstack($this->type);
        if(count($errors = app::getValidator('structure_api_codename')->validate($data)) > 0) {
            return false;
        }

        $this->authentication = new \codename\core\datacontainer(array(
                'app_secret' => $data['secret'],
                'app_name' => $data['app_name']
        ));

        $this->serviceprovider = new \codename\core\value\structure\api\codename\serviceprovider(array(
                'host' => $data['host'],
                'port' => $data['port']
        ));
        return $this;
    }

    /**
     * Returns the cacheGroup for this instance
     * @return string
     */
    protected function getCachegroup() : string {
        return 'API_' . $this->type . '_' . $this->authentication->getData('app_name');
    }

    /**
     * Mapper for the request function.
     * <br />This method will concatenate the URL and return the (void) result of doRequest($url).
     * @param string $url
     * @return mixed
     */
    public function request(string $url) {
        return $this->doRequest($this->serviceprovider->getUrl() . $url);
    }

    /**
     * Sets data for the request to be sent.
     * <br />Will erialize arrays as JSON.
     * @param array $data
     * @return void
     */
    public function setData(array $data) {
        foreach($data as $key => $value) {
            if(is_array($value)) {
                if((count($value) > 0) && (reset($value) instanceof \CURLFile) ) {
                  // add the CURLFile as a POST content
                  $this->addData($key, $value);
                  continue;
                } else {
                  $value = json_encode($value);
                }
            }
          $this->addData($key, $value);
        }
        return;
    }

    /**
     * Adds another key to the data array of this instance.
     * <br />Will check for the forbiddenpostfields here and do nothing if the field's $name is forbidden
     * @param string $name
     * @param multitype $value
     * @return void
     */
    public function addData(string $name, $value) {
        if(in_array($name, $this->forbiddenpostfields)) {
            return;
        }
        $this->data[$name] = $value;
        return;
    }

    /**
     * Returns the errorstack of the API instance
     * @return \codename\core\errorstack
     */
    public function getErrorstack() : \codename\core\errorstack {
        return $this->errorstack;
    }

    /**
     * Hashes the type, app, secret and salt of this instance and returns the hash value
     * @return string
     **/
    protected function makeHash() : string {
        if(strlen($this->salt) == 0) {
            $this->errorstack->addError('setup', 'SERVICE_SALT_NOT_FOUND');
            print_r($this->errorstack->getErrors());
        }
        if(strlen($this->type) == 0) {
            $this->errorstack->addError('setup', 'TYPE_NOT_FOUND');
            print_r($this->errorstack->getErrors());
        }
        return hash('sha512', $this->type . $this->authentication->getData('app_name') . $this->authentication->getData('app_secret') . $this->salt);
    }

    /**
     * Uses the given $version and $endpoint to request the correct API host and endpoint URL
     * @param string $version
     * @param string $endpoint
     * @return bool
     */
    protected function doAPIRequest(string $version, string $endpoint) : bool {
        return $this->doRequest($this->serviceprovider->getUrl() . '/' . $version . '/' . $endpoint);
    }

    /**
     * Performs the request
     * @param string $type
     * @return mixed|bool
     */
    protected function doRequest(string $url) {
        $this->curlHandler = curl_init();

        curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curlHandler, CURLOPT_URL, $url);
        curl_setopt($this->curlHandler, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, array(
                "X-App: " . $this->authentication->getData('app_name'),
                "X-Auth: " . $this->makeHash()
        ));

        $this->sendData();
        // app::getLog('codenameapi')->debug(serialize($this));

        $res = $this->decodeResponse(curl_exec($this->curlHandler));

        curl_close($this->curlHandler);

        if(is_bool($res) && !$res) {
            return false;
        }

        return $res;
    }

    /**
     * If data exist, this function will write the data as POST fields to the curlHandler
     * @return void
     */
    protected function sendData() {
        if(count($this->data) > 0) {
            curl_setopt($this->curlHandler, CURLOPT_POST, 1);
            foreach($this->data as $key => &$value) {
                if(is_array($value)) {
                    if(count($value) > 0 && !(reset($value) instanceof \CURLFile)) {
                      $value = json_encode($value);
                    }
                }
            }
            curl_setopt($this->curlHandler, CURLOPT_POST, count($this->data));
            curl_setopt($this->curlHandler, CURLOPT_POSTFIELDS, $this->data);
        }
        return;
    }

    /**
     * Decodes the response and validates it
     * <br />Uses validators (\codename\core\validator\structure\api\response) to check the response content
     * <br />Will return false on any error.
     * <br />Will output cURL errors on development environments
     * @param string $response
     * @return mixed
     */
    protected function decodeResponse(string $response) {
        app::getLog('debug')->debug('CORE_BACKEND_CLASS_API_CODENAME_DECODERESPONSE::START ($response = ' . $response . ')');

        if(defined('CORE_ENVIRONMENT') && CORE_ENVIRONMENT == 'dev') {
            print_r(curl_error($this->curlHandler));
        }

        if(strlen($response) == 0) {
            $this->response = null;
            app::getLog('errormessage')->warning('CORE_BACKEND_CLASS_API_CODENAME_DECODERESPONSE::RESPONSE_EMPTY ($response = ' . $response . ')');
            return false;
        }

        $response = app::object2array(json_decode($response));

        if(is_null($response)) {
            $this->response = null;
            return false;
        }

        if(count($errors = app::getValidator('structure_api_codename_response')->validate($response)) > 0) {
            app::getLog('errormessage')->warning('CORE_BACKEND_CLASS_API_CODENAME_DECODERESPONSE::RESPONSE_INVALID ($response = ' . json_encode($response) . ')');
            return false;
        }

        $this->response = $response;
        if(array_key_exists('errors', $response)) {
            app::getLog('errormessage')->warning('CORE_BACKEND_CLASS_API_CODENAME_DECODERESPONSE::RESPONSE_CONTAINS_ERRORS ($response = ' . json_encode($response) . ')');
            return false;
        }

        return $response;
    }

}
