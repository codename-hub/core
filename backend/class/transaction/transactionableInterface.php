<?php
namespace codename\core\transaction;

interface transactionableInterface {

  /**
   * [beginTransaction description]
   * @param  string $transactionName [description]
   * @return [type]                  [description]
   */
  function beginTransaction(string $transactionName);

  /**
   * [endTransaction description]
   * @param  string $transactionName [description]
   * @return [type]                  [description]
   */
  function endTransaction(string $transactionName);

}
