<?php
namespace codename\core\api;
use \codename\core\app;
use codename\core\exception;

/**
 * Extension for \codename\core\api\codename using standardized rest api endpoint calling
 * @package core
 * @author Kevin Dargel
 */
class rest extends \codename\core\api\codename {

  /**
   * @inheritDoc
   * @param array $data [data array]
   */
  public function __CONSTRUCT(array $data)
  {
    $value = parent::__CONSTRUCT($data);
    return $value;
  }

  /**
   * [prepareRequest description]
   * @param  string $url    [description]
   * @param  string $method [description]
   * @param  array  $params [description]
   * @return [type]         [description]
   */
  protected function prepareRequest(string $url, string $method, array $params = []) {
    $this->curlHandler = curl_init();

    curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYHOST, 0);

    if(!in_array($method, ['GET', 'PUT', 'POST', 'PATCH', 'DELETE', 'OPTIONS' ])) {
      throw new exception('EXCEPTION_CORE_API_REST_INVALID_METHOD', exception::$ERRORLEVEL_ERROR, $method);
    }

    if($method == 'POST') {
      curl_setopt($this->curlHandler, CURLOPT_POST, 1);
    } else {
      curl_setopt($this->curlHandler, CURLOPT_POST, 0);
      if($method != 'GET') {
        // custom method, either 'PUT', 'POST', 'PATCH', 'DELETE', 'OPTIONS' ... ?
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
      } else {
        //
        // HTTP Method GET
        // handle URL-based params
        // as it is 'GET' and not POST.
        //
        // so, we merge-in the params into the url
        //

        if(count($params) > 0) {
          // NOTE: \http\Url is some of the worst PECL exts and class constructs I've ever seen
          // hardly documented, but similar behaviour to the old parse_url and comparable stuff.
          $url = (new \http\Url($url))->mod([
            'query' => http_build_query($params)
          ])->toString();
        }

        // $url = $url . '/?'.http_build_query($params);
      }
    }

    // this may be done in sendData()
    /* if(count($params) > 0) {
      curl_setopt($this->curlHandler, CURLOPT_POSTFIELDS, $params);
    }*/

    curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($this->curlHandler, CURLOPT_URL, $url);
    curl_setopt($this->curlHandler, CURLOPT_RETURNTRANSFER, true);
  }

  /**
   * @inheritDoc
   */
  protected function doRequest(string $url, string $method = '', array $params = [])
  {
    $this->prepareRequest($url, $method, $params);

    /*
    $this->curlHandler = curl_init();

    curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYHOST, 0);

    if(!in_array($method, ['GET', 'PUT', 'POST', 'PATCH', 'DELETE', 'OPTIONS' ])) {
      throw new exception('EXCEPTION_CORE_API_REST_INVALID_METHOD', exception::$ERRORLEVEL_ERROR, $method);
    }

    if($method == 'POST') {
      curl_setopt($this->curlHandler, CURLOPT_POST, 1);
    } else {
      curl_setopt($this->curlHandler, CURLOPT_POST, 0);
      if($method != 'GET') {
        // custom method, either 'PUT', 'POST', 'PATCH', 'DELETE', 'OPTIONS' ... ?
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
      }
    }

    curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($this->curlHandler, CURLOPT_URL, $url);
    curl_setopt($this->curlHandler, CURLOPT_RETURNTRANSFER, true);
    */
    // echo "$url";

    /* curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, array(
            "X-App: " . $this->authentication->getData('app_name'),
            "X-Auth: " . $this->makeHash()
    )); */

    $this->sendData();
    // app::getLog('codenameapi')->debug(serialize($this));

    $res = $this->decodeResponse(curl_exec($this->curlHandler));

    if(!$res) {
      curl_error($this->curlHandler);
    }

    curl_close($this->curlHandler);

    if(is_bool($res) && !$res) {
      // we may throw an exception here
      return false;
    }

    return $res;
  }

}
