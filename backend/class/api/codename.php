<?php

namespace codename\core\api;

use codename\core\api;
use codename\core\app;
use codename\core\datacontainer;
use codename\core\errorstack;
use codename\core\exception;
use codename\core\value\structure\api\codename\serviceprovider;
use CURLFile;
use CurlHandle;
use ReflectionException;

/**
 * Extension for \codename\core\api representing the clients for our own API Service Providers
 * Also @see \codename\core\context\api for our own API Service provider's structure
 * @package core
 * @since 2016-04-05
 */
class codename extends api
{
    /**
     * Contains a list of fields that must not be sent via the POST request
     * Most of the given fields may irritate the foreign service as they are based on the core
     * @internal This is since these fields are request arguments responsible for app routing.
     * @var array
     */
    public $forbiddenpostfields = ['app', 'context', 'view', 'action', 'callback', 'template', 'lang'];
    /**
     * Contains the application data
     * @var datacontainer
     */
    protected datacontainer $authentication;
    /**
     * Contains configuration of the service provider (host, port, etc)
     * @var serviceprovider
     */
    protected serviceprovider $serviceprovider;
    /**
     * Contains the CURL Handler for the next request
     * Is used to handle HTTP(s) requests for retrieving and sending data from the foreign service
     * @var CurlHandle
     */
    protected CurlHandle $curlHandler;
    /**
     * What is the request's special secret string?
     * Many codename API services are relying on a second authentication factor.
     * By definition the second factor is dependent from the concrete topic of the request
     * The given salt is filled with the requested key's name.
     * So every different key has a different salt.
     * @internal Will not be transferred unencrypted
     * @var string
     */
    protected string $salt = '';
    /**
     * Contains the API service provider's response to the given request
     * After retrieving a response from the foreign host, it will be stored here.
     * @var mixed
     */
    protected mixed $response = '';
    /**
     * Contains the API type.
     * Typically, is defined using the name of the foreign service in upper-case characters
     * @example YOURAPITYPE
     * @var string
     */
    protected string $type = '';
    /**
     * Contains POST data for the request
     * Typically all headers for authentication and data retrieval
     * @var array
     */
    protected array $data = [];
    /**
     * headers to send with the request
     * @var array
     */
    protected array $headers = [];

    /**
     * Create instance
     * @param array $data
     * @throws ReflectionException
     * @throws exception
     */
    public function __construct(array $data)
    {
        parent::__construct();
        if (count(app::getValidator('structure_api_codename')->validate($data)) > 0) {
            return false;
        }

        $this->authentication = new datacontainer([
          'app_secret' => $data['secret'],
          'app_name' => $data['app_name'],
        ]);

        $this->serviceprovider = new serviceprovider([
          'host' => $data['host'],
          'port' => $data['port'],
        ]);
        return $this;
    }

    /**
     * Mapper for the request function.
     * This method will concatenate the URL and return the (void) result of doRequest($url).
     * @param string $url
     * @return mixed
     * @throws ReflectionException
     * @throws exception
     */
    public function request(string $url): mixed
    {
        return $this->doRequest($this->serviceprovider->getUrl() . $url);
    }

    /**
     * Performs the request
     * @param string $url
     * @return mixed
     * @throws ReflectionException
     * @throws exception
     */
    protected function doRequest(string $url): mixed
    {
        $this->curlHandler = curl_init();

        curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curlHandler, CURLOPT_URL, $url);
        curl_setopt($this->curlHandler, CURLOPT_RETURNTRANSFER, true);

        $preparedHeaders = [];
        foreach ($this->headers as $k => $v) {
            $preparedHeaders[] = $k . ": " . $v;
        }

        //
        // NOTE: doRequest() might be overwritten/re-implemented
        // in derived classes. Don't forget to set headers there.
        //
        curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, $preparedHeaders);

        $this->sendData();

        $response = curl_exec($this->curlHandler);
        $res = $this->decodeResponse($response);

        curl_close($this->curlHandler);

        // WARNING: reset data after request is needed
        // to prevent information leakage to following requests.
        $this->resetData();

        if (is_bool($res) && !$res) {
            return false;
        }

        return $res;
    }

    /**
     * If data exist, this function will write the data as POST fields to the curlHandler
     * @return void
     */
    protected function sendData(): void
    {
        if (count($this->data) > 0) {
            curl_setopt($this->curlHandler, CURLOPT_POST, 1);
            foreach ($this->data as &$value) {
                if (is_array($value)) {
                    if (count($value) > 0 && !(reset($value) instanceof CURLFile)) {
                        $value = json_encode($value);
                    }
                }
            }
            curl_setopt($this->curlHandler, CURLOPT_POST, count($this->data));
            curl_setopt($this->curlHandler, CURLOPT_POSTFIELDS, $this->data);
        }
    }

    /**
     * Decodes the response and validates it
     * Uses validators (\codename\core\validator\structure\api\response) to check the response content
     * Will return false on any error.
     * Will output cURL errors on development environments
     * @param string $response
     * @return mixed
     * @throws ReflectionException
     * @throws exception
     */
    protected function decodeResponse(string $response): mixed
    {
        app::getLog('debug')->debug('CORE_BACKEND_CLASS_API_CODENAME_DECODERESPONSE::START ($response = ' . $response . ')');

        if (defined('CORE_ENVIRONMENT') && CORE_ENVIRONMENT == 'dev') {
            print_r(curl_error($this->curlHandler));
        }

        if (strlen($response) == 0) {
            $this->response = null;
            app::getLog('errormessage')->warning('CORE_BACKEND_CLASS_API_CODENAME_DECODERESPONSE::RESPONSE_EMPTY ($response = ' . $response . ')');
            return false;
        }

        $response = app::object2array(json_decode($response));

        if (count(app::getValidator('structure_api_codename_response')->validate($response)) > 0) {
            app::getLog('errormessage')->warning('CORE_BACKEND_CLASS_API_CODENAME_DECODERESPONSE::RESPONSE_INVALID ($response = ' . json_encode($response) . ')');
            return false;
        }

        $this->response = $response;
        if (array_key_exists('errors', $response)) {
            app::getLog('errormessage')->warning('CORE_BACKEND_CLASS_API_CODENAME_DECODERESPONSE::RESPONSE_CONTAINS_ERRORS ($response = ' . json_encode($response) . ')');

            //
            //  Push errors to errorstack
            //
            $this->errorstack->addErrors($response['errors']);
            return false;
        }

        return $response;
    }

    /**
     * Resets internal data storage
     * e.g. after firing a request
     * or on purpose. This WON'T reset prepared headers
     */
    public function resetData(): void
    {
        $this->data = [];
    }

    /**
     * Sets data for the request to be sent.
     * Will serialize arrays as JSON.
     * @param array $data
     * @return void
     */
    public function setData(array $data): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if ((count($value) > 0) && (reset($value) instanceof CURLFile)) {
                    // add the CURLFile as a POST content
                    $this->addData($key, $value);
                    continue;
                } else {
                    $value = json_encode($value);
                }
            }
            $this->addData($key, $value);
        }
    }

    /**
     * Adds another key to the data array of this instance.
     * Will check for the forbiddenpostfields here and do nothing if the field's $name is forbidden
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function addData(string $name, mixed $value): void
    {
        if (in_array($name, $this->forbiddenpostfields)) {
            return;
        }
        $this->data[$name] = $value;
    }

    /**
     * Returns the errorstack of the API instance
     * @return errorstack
     */
    public function getErrorstack(): errorstack
    {
        return $this->errorstack;
    }

    /**
     * Returns the cacheGroup for this instance
     * @return string
     */
    protected function getCacheGroup(): string
    {
        return 'API_' . $this->type . '_' . $this->authentication->getData('app_name');
    }

    /**
     * Hashes the type, app, secret and salt of this instance and returns the hash value
     * @return string
     **/
    protected function makeHash(): string
    {
        if (strlen($this->salt) == 0) {
            $this->errorstack->addError('setup', 'SERVICE_SALT_NOT_FOUND');
            print_r($this->errorstack->getErrors());
        }
        if (strlen($this->type) == 0) {
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
     * @throws ReflectionException
     * @throws exception
     */
    protected function doAPIRequest(string $version, string $endpoint): bool
    {
        return $this->doRequest($this->serviceprovider->getUrl() . '/' . $version . '/' . $endpoint);
    }

    /**
     * [setHeader description]
     * @param string $key [description]
     * @param [type] $value [description]
     */
    protected function setHeader(string $key, $value): void
    {
        if ($value === null) {
            unset($this->headers[$key]);
        }
        $this->headers[$key] = $value;
    }
}
