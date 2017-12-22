<?php
namespace codename\core\request;

/**
 * I handle all the data for a HTTP request
 * @package core
 * @since 2016-05-31
 */
class http extends \codename\core\request {

    /**
     * @inheritDoc
     */
    public function __construct(array $data = array())
    {
      parent::__construct($data);
      $this->addData($_GET);
      $this->addData($_POST);
      $this->setData('lang', "de_DE");
    }

}
