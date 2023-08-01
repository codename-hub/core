<?php

namespace codename\core\api;

use codename\core\exception;
use http\Url;

/**
 * Extension for \codename\core\api\codename using standardized rest api endpoint calling
 * @package core
 */
class rest extends codename
{
    /**
     * {@inheritDoc}
     * @param array $data [data array]
     */
    public function __construct(array $data)
    {
        return parent::__construct($data);
    }

    /**
     * {@inheritDoc}
     */
    protected function doRequest(string $url, string $method = '', array $params = []): mixed
    {
        $this->prepareRequest($url, $method, $params);

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


        if (!$res) {
            $err = curl_error($this->curlHandler);
            if ($err !== '') {
                $this->errorstack->addError('curl', 0, $err);
            }
        }

        curl_close($this->curlHandler);

        // WARNING: reset data after request is needed
        // to prevent information leakage to following requests.
        $this->resetData();

        if (is_bool($res) && !$res) {
            // we may throw an exception here
            return false;
        }

        return $res;
    }

    /**
     * [prepareRequest description]
     * @param string $url [description]
     * @param string $method [description]
     * @param array $params [description]
     * @return void [type]         [description]
     * @throws exception
     */
    protected function prepareRequest(string $url, string $method, array $params = []): void
    {
        $this->curlHandler = curl_init();

        curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYHOST, 0);

        if (!in_array($method, ['GET', 'PUT', 'POST', 'PATCH', 'DELETE', 'OPTIONS'])) {
            throw new exception('EXCEPTION_CORE_API_REST_INVALID_METHOD', exception::$ERRORLEVEL_ERROR, $method);
        }

        if ($method == 'POST') {
            curl_setopt($this->curlHandler, CURLOPT_POST, 1);
            $this->setData($params);
        } else {
            curl_setopt($this->curlHandler, CURLOPT_POST, 0);
            if ($method != 'GET') {
                // custom method, either 'PUT', 'POST', 'PATCH', 'DELETE', 'OPTIONS' ... ?
                curl_setopt($this->curlHandler, CURLOPT_CUSTOMREQUEST, $method);
            } elseif (count($params) > 0) {
                // NOTE: \http\Url is some of the worst PECL texts and class constructs I've ever seen
                // hardly documented, but similar behaviour to the old parse_url and comparable stuff.
                $url = (new Url($url))->mod([
                  'query' => http_build_query($params, null, '&', PHP_QUERY_RFC3986),
                ])->toString();
            }
        }

        curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curlHandler, CURLOPT_URL, $url);
        curl_setopt($this->curlHandler, CURLOPT_RETURNTRANSFER, true);
    }
}
