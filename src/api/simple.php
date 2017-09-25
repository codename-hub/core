<?php
namespace codename\core\api;
use \codename\core\app;

/**
 * Extension for \codename\core\api\codename using a simplified Endpoint-calling (via POST only, no speaking URLs!)
 * @package core
 * @author Kevin Dargel
 * @since 2016-11-28
 */
class simple extends \codename\core\api\codename {

  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(array $data)
  {
    $value = parent::__CONSTRUCT($data);
    return $value;
  }

  /**
   * @inheritDoc
   */
  protected function doAPIRequest(string $version, string $endpoint): bool
  {
    // fill DATA with the to-be-used version and endpoint parameters
    $this->data['version'] = $version;
    $this->data['endpoint'] = $endpoint;
    // Just main page/endpoint without any flimflam.
    return $this->doRequest($this->serviceprovider->getHost(). ':' . $this->serviceprovider->getPort());
  }

  /**
   * @inheritDoc
   */
  protected function doRequest(string $url): bool
  {
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
    app::getLog('codenameapi')->debug(serialize($this));

    $res = $this->decodeResponse(curl_exec($this->curlHandler));

    curl_close($this->curlHandler);

    if(is_bool($res) && !$res) {
        return false;
    }

    return true;
  }

}
