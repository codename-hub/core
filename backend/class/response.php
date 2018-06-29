<?php
namespace codename\core;

/**
 * Accessing and storing data that is processed on the template
 * @package core
 * @since 2016-01-25
 */
abstract class response extends \codename\core\datacontainer {

    /**
     * Status Constant: Successful response
     * @var string
     */
    public const STATUS_SUCCESS = 'STATUS_SUCCESS';

    /**
     * Status Constant: Errorneous response
     * @var string
     */
    public const STATUS_INTERNAL_ERROR = 'STATUS_INTERNAL_ERROR';

    /**
     * Status Constant: NotFound response
     * @var string
     */
    public const STATUS_NOTFOUND = 'STATUS_NOTFOUND';

    /**
     * Status Constant: AccessDenied response
     * @var string
     */
    public const STATUS_ACCESS_DENIED = 'STATUS_ACCESS_DENIED';

    /**
     * Status Constant: Forbidden response
     * @var string
     */
    public const STATUS_FORBIDDEN = 'STATUS_FORBIDDEN';

    /**
     * Status Constant: Unauthenticated response
     * @var string
     */
    public const STATUS_UNAUTHENTICATED = 'STATUS_UNAUTHENTICATED';

    /**
     * Contains the derived output
     * @var string
     */
    protected $output = '';

    /**
     * I contain various frontend resources
     * @var array
     */
    protected $resources = array();

    /**
     * Creates instance and sets the data equal to the request container
     * @return response
     */
    public function __CONSTRUCT() {
        $this->status = $this->getDefaultStatus();
        $this->addData(array(
            'context' => app::getRequest()->getData('context'),
            'view' => app::getRequest()->getData('view'),
            'action' => app::getRequest()->getData('action'),
            'template' => app::getRequest()->getData('template')
        ));

        // set appserver header
        $this->setHeader("APP-SRV: " . gethostname());

        return $this;
    }

    /**
     * [getDefaultStatus description]
     * @return string
     */
    protected function getDefaultStatus() {
      return self::STATUS_SUCCESS;
    }

    /**
     * [protected description]
     * @var [type]
     */
    protected $status = null;

    /**
     * [setStatus description]
     * @param string $status [description]
     */
    public function setStatus(string $status) {
      $this->status = $status;
    }

    /**
     * sets a header
     * mostly for http responses
     * @param string $header
     */
    public abstract function setHeader(string $header);

    /**
     * [setRedirect description]
     * @param string $string  [description]
     * @param [type] $context [description]
     * @param [type] $view    [description]
     * @param [type] $action  [description]
     */
    public function setRedirect(string $string, string $context = null, string $view = null, string $action = null) {
      return;
    }

    /**
     * perform the configured redirect
     */
    public function doRedirect() {
      return;
    }

    /**
     * [getStatuscode description]
     * @return int [description]
     */
    public function getStatuscode() : int {
      return $this->translateStatus();
    }

    /**
     * translate current internal status to a responsetype specific one
     * @var [type]
     */
    protected abstract function translateStatus();

    /**
     * Push output to whatever we're outputting to.
     * Depends on the response type (inherited class)
     * @return void
     */
    public abstract function pushOutput();

    /**
     * Returns the output content of this response
     * @return string
     */
    public function getOutput() : string {
        return $this->output;
    }

    /**
     * Sets the output of this response
     * @param string $output
     * @return void
     */
    public function setOutput(string $output) {
        $this->output = $output;
        return;
    }

    /**
     * [displayException description]
     * @param  Exception $e [description]
     * @return [type]       [description]
     */
    public function displayException(\Exception $e) {
        echo($e->getMessage());
    }

}
