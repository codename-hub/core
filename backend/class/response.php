<?php

namespace codename\core;

/**
 * Accessing and storing data that is processed on the template
 * @package core
 * @since 2016-01-25
 */
abstract class response extends datacontainer
{
    /**
     * Status Constant: Successful response
     * @var string
     */
    public const STATUS_SUCCESS = 'STATUS_SUCCESS';

    /**
     * Status Constant: Erroneous response
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
     * Status Constant: Bad request
     * @var string
     */
    public const STATUS_BAD_REQUEST = 'STATUS_BAD_REQUEST';

    /**
     * Status Constant: Unauthenticated response
     * @var string
     */
    public const STATUS_UNAUTHENTICATED = 'STATUS_UNAUTHENTICATED';

    /**
     * Status Constant: request performed includes too much data to be handled
     * mostly relevant for HTTP requests and less CLI ...
     * @var string
     */
    public const STATUS_REQUEST_SIZE_TOO_LARGE = 'STATUS_REQUEST_SIZE_TOO_LARGE';

    /**
     * Contains the derived output
     * @var string
     */
    protected string $output = '';

    /**
     * I contain various frontend resources
     * @var array
     */
    protected array $resources = [];
    /**
     * [protected description]
     * @var [type]
     */
    protected $status = null;

    /**
     * Creates instance and sets the data equal to the request container
     * @return response
     */
    public function __construct()
    {
        parent::__construct();
        $this->status = $this->getDefaultStatus();
        $this->addData([
          'context' => app::getRequest()->getData('context'),
          'view' => app::getRequest()->getData('view'),
          'action' => app::getRequest()->getData('action'),
          'template' => app::getRequest()->getData('template'),
        ]);

        // set appserver header
        $this->setHeader("APP-SRV: " . gethostname());

        return $this;
    }

    /**
     * [getDefaultStatus description]
     * @return string
     */
    protected function getDefaultStatus(): string
    {
        return self::STATUS_SUCCESS;
    }

    /**
     * sets a header
     * mostly for http responses
     * @param string $header
     */
    abstract public function setHeader(string $header);

    /**
     * [setStatus description]
     * @param string $status [description]
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * [setRedirect description]
     * @param string $string [description]
     * @param string|null $context
     * @param string|null $view
     * @param string|null $action
     */
    public function setRedirect(string $string, string $context = null, string $view = null, string $action = null)
    {
    }

    /**
     * perform the configured redirect
     */
    public function doRedirect()
    {
    }

    /**
     * [getStatuscode description]
     * @return int [description]
     */
    public function getStatuscode(): int
    {
        return $this->translateStatus();
    }

    /**
     * translate current internal status to a response type specific one
     * @var [type]
     */
    abstract protected function translateStatus();

    /**
     * Push output to whatever we're outputting to.
     * Depends on the response type (inherited class)
     * @return void
     */
    abstract public function pushOutput(): void;

    /**
     * Returns the output content of this response
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * Sets the output of this response
     * @param string $output
     * @return void
     */
    public function setOutput(string $output): void
    {
        $this->output = $output;
    }

    /**
     * [displayException description]
     * @param \Exception $e [description]
     * @return void [type]       [description]
     */
    public function displayException(\Exception $e): void
    {
        echo($e->getMessage());
    }
}
