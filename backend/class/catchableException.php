<?php

namespace codename\core;

/**
 * Why should we even use exceptions, if we can't catch them?
 * I've to catch 'em all!
 */
class catchableException extends exception
{
    /**
     * Create an errormessage
     * @param string $code
     * @param int $level
     * @param mixed|null $info
     */
    public function __construct(string $code, int $level, $info = null)
    {
        parent::__construct($code, $level, $info);
        $this->message = $this->translateExceptionCode($code);
        $this->code = $code;
        $this->info = $info;
        app::getHook()->fire($code);
        app::getHook()->fire('EXCEPTION');
        return $this;
    }
}
