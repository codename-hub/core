<?php

namespace codename\core\transaction;

interface transactionableInterface
{
    /**
     * [beginTransaction description]
     * @param string $transactionName [description]
     * @return void [type]                  [description]
     */
    public function beginTransaction(string $transactionName): void;

    /**
     * [endTransaction description]
     * @param string $transactionName [description]
     * @return void [type]                  [description]
     */
    public function endTransaction(string $transactionName): void;
}
