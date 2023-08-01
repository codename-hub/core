<?php

namespace codename\core;

use codename\core\transaction\transactionableInterface;

/**
 * [transaction description]
 */
class transaction
{
    /**
     * [protected description]
     * @var string|null [type]
     */
    protected ?string $name = null;
    /**
     * [protected description]
     * @var array
     */
    protected array $transactionables = [];
    /**
     * [protected description]
     * @var bool [type]
     */
    protected bool $started = false;

    /**
     * [__construct description]
     * @param string $name [description]
     * @param array $transactionables [description]
     */
    public function __construct(string $name, array $transactionables = [])
    {
        $this->name = $name;
        $this->transactionables = $transactionables;
    }

    /**
     * [addTransactionable description]
     * @param transactionableInterface $transactionable [description]
     * @return transaction                               [description]
     */
    public function addTransactionable(transactionableInterface $transactionable): transaction
    {
        $this->transactionables[] = $transactionable;
        // add a transactionable after transaction has been started
        if ($this->started) {
            $transactionable->beginTransaction($this->name);
        }
        return $this;
    }

    /**
     * [start description]
     * @return transaction [description]
     * @throws exception
     */
    public function start(): transaction
    {
        if ($this->started) {
            throw new exception('EXCEPTION_TRANSACTION_START_ALREADY_STARTED', exception::$ERRORLEVEL_FATAL, $this->name);
        }
        $this->started = true;
        foreach ($this->transactionables as $transactionable) {
            $transactionable->beginTransaction($this->name);
        }
        return $this;
    }

    /**
     * [end description]
     * @return transaction [description]
     * @throws exception
     */
    public function end(): transaction
    {
        if (!$this->started) {
            throw new exception('EXCEPTION_TRANSACTION_END_NOT_STARTED', exception::$ERRORLEVEL_FATAL, $this->name);
        }
        foreach ($this->transactionables as $transactionable) {
            $transactionable->endTransaction($this->name);
        }
        $this->started = false;
        return $this;
    }
}
