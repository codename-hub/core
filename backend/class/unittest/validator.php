<?php
namespace codename\core\unittest;

/**
 * I am just an extender for the unittest class
 * @package codename\core
 * @since 2016-11-02
 */
class validator extends \codename\core\unittest {

  /**
   * [getValidator description]
   * @return codenamecorevalidator [description]
   */
  protected function getValidator() : \codename\core\validator {
    // load the respective validator via namespace, by instanciated class name
    // we have to remove __CLASS__ (THIS exact class here)
    $validator = \codename\core\app::getValidator(str_replace(__CLASS__.'\\', '', (new \ReflectionClass($this))->getName()));
    $validator->reset();
    return $validator;
  }

}
