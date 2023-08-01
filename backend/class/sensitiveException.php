<?php

namespace codename\core;

/**
 * Exception class encapsulating sensitive data
 * which means:
 * - data which may contain personal information
 * - passwords
 * - API keys
 * - internal application hosts & access keys
 * - etc.
 */
class sensitiveException extends exception
{
    /**
     * [protected description]
     * @var null|\Exception
     */
    protected ?\Exception $encapsulatedException = null;

    /**
     * @param \Exception $encapsulatedException
     */
    public function __construct(\Exception $encapsulatedException)
    {
        $this->encapsulatedException = $encapsulatedException;
        $this->message = $encapsulatedException->getMessage();
        $this->line = $encapsulatedException->getLine();
        $this->file = $encapsulatedException->getFile();
        if ($encapsulatedException instanceof exception) {
            $this->info = $encapsulatedException->info;
        }
    }

    /**
     * [getEncapsulatedException description]
     * @return \Exception [description]
     */
    public function getEncapsulatedException(): \Exception
    {
        return $this->encapsulatedException;
    }
}
