<?php
namespace codename\core\request;

/**
 * I handle all the data for a HTTP request
 * @package core
 * @since 2016-05-31
 */
class http extends \codename\core\request {

    /**
     * Contains data for redirecting the user after finishing the request
     * @var array | string
     */
    protected $redirect = null;

    /**
     * Redirects the user at some point to the given destination.
     * <br >Either pass a valid URL to the function (including protocol!) or pass the app/context/view/action data
     * @param string $string (URL / app)
     * @param string $context
     * @param string $view
     * @param string $action
     * @param array $params
     * @return void
     */
    public function setRedirect(string $string, string $context = null, string $view = null, string $action = null) {
        if(strpos($string, '://') != false || strpos($string, '/') === 0) {
            $this->redirect = $string;
            return;
        }
        $this->redirect = array(
            'app' => $string,
            'context' => $context,
            'view' => $view,
            'action' => $action
        );
        return;
    }

    /**
     * Sets parameters used for redirection
     * @param array
     */
    public function setRedirectArray(array $param) {
      $this->redirect = $param;
      return;
    }

    /**
     * This function performs the redirection by using a forward header ("Location: $url").
     * @return void
     * @todo make a makeUrl function for the parameters
     */
    public function doRedirect() {
        if(is_null($this->redirect)) {
            return;
        }

        if(is_string($this->redirect)) {
            header("Location: " . $this->redirect);
        }

        if(is_array($this->redirect)) {
            $url = '/?' . http_build_query($this->redirect);
            header("Location: " . $url);
        }
        return;
    }


}
