<?php

namespace codename\core;

/**
 * We override the php's exception class to handle our exceptions by our own exception handler
 * @package core
 * @since 2016-01-05
 */
class exception extends \Exception
{
    /**
     * @var int
     */
    public static int $ERRORLEVEL_TRIVIAL = -3;
    /**
     * @var int
     */
    public static int $ERRORLEVEL_DEBUG = -2;
    /**
     * @var int
     */
    public static int $ERRORLEVEL_NOTICE = -1;
    /**
     * @var int
     */
    public static int $ERRORLEVEL_NORMAL = 0;
    /**
     * @var int
     */
    public static int $ERRORLEVEL_WARNING = 1;
    /**
     * @var int
     */
    public static int $ERRORLEVEL_ERROR = 2;
    /**
     * @var int
     */
    public static int $ERRORLEVEL_FATAL = 3;

    /**
     * additional information
     * @var null|mixed
     */
    public $info;

    /**
     * Create an errormessage that will stop execution of this request.
     * @param string $code
     * @param int $level
     * @param mixed $info
     */
    public function __construct(string $code, int $level, $info = null)
    {
        $this->message = $this->translateExceptionCode($code);
        $this->code = $code;
        $this->info = $info;

        app::getHook()->fire($code);
        app::getHook()->fire('EXCEPTION');
    }

    /**
     * [translateExceptionCode description]
     * @param string $code [description]
     * @return string       [description]
     */
    protected function translateExceptionCode(string $code): string
    {
        return $code;
    }
}
