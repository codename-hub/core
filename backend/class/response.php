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
            'context' => app::getInstance('request')->getData('context'),
            'view' => app::getInstance('request')->getData('view'),
            'action' => app::getInstance('request')->getData('action'),
            'template' => app::getInstance('request')->getData('template')
        ));
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
