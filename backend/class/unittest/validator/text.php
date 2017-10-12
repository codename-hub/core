<?php
namespace codename\core\unittest\validator;

/**
 * I am just an extender for the unittest class
 * @package codename\core
 * @since 2016-11-02
 */
class text extends \codename\core\unittest\validator {

  /**
   * {@inheritDoc}
   */
  public function testAll() {
    $this->testValueNotAString();
    return;
  }

  /**
   * simple non-text value test
   * @return void
   */
  public function testValueNotAString() {
    $this->assertEquals('VALIDATION.VALUE_NOT_A_STRING', $this->getValidator()->validate(array())[0]['__CODE'] );
  }

}
