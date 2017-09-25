<?php
namespace codename\core;

/**
 * Accessing and storing data that is processed on the template
 * @package core
 * @since 2016-01-25
 */
class response extends \codename\core\datacontainer {

    /**
     * Contains the derived output
     * @var strubg
     */
    protected $output = '';

    /**
     * I contain various frontend resources
     * @var array
     */
    protected $resources = array();

    /**
     * Contains the status code
     * @var int
     */
    protected $statusCode = 200;

    /**
     * Contains the status text
     * @var string
     */
    protected $statusText = 'OK';

    /**
     * Creates instance and sets the data equal to the request container
     * @return response
     */
    public function __CONSTRUCT() {
        $this->addData(array(
            'context' => app::getInstance('request')->getData('context'),
            'view' => app::getInstance('request')->getData('view'),
            'action' => app::getInstance('request')->getData('action'),
            'template' => app::getInstance('request')->getData('template')
        ));
        return $this;
    }

    /**
     * Returns the status code of the current response
     * @return int
     */
    public function getStatuscode() : int {
        return $this->statusCode;
    }

    /**
     * Helper to set HTTP status codes
     * @param int $statusCode
     * @param string $statusText
     */
    public function setStatuscode(int $statusCode, string $statusText) : \codename\core\response {
        $this->statusCode = $statusCode;
        $this->statusText = $statusText;
        return $this;
    }

    /**
     * Actually sends output to the response HTTP Stream
     * @return void
     */
    public function pushOutput() {
        echo $this->getOutput();
        return;
    }

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

}
