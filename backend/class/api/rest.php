<?php
namespace codename\core\api;
use \codename\core\app;

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
   * @inheritDoc
   */
  protected function doRequest(string $url)
  {
    $this->curlHandler = curl_init();

    curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($this->curlHandler, CURLOPT_URL, $url);
    curl_setopt($this->curlHandler, CURLOPT_RETURNTRANSFER, true);

    echo "$url";

    /* curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, array(
            "X-App: " . $this->authentication->getData('app_name'),
            "X-Auth: " . $this->makeHash()
    )); */

    $this->sendData();
    // app::getLog('codenameapi')->debug(serialize($this));

    $res = $this->decodeResponse(curl_exec($this->curlHandler));

    curl_close($this->curlHandler);

    if(is_bool($res) && !$res) {
        return false;
    }

    return $res;
  }

}
