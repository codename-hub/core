<?php
namespace codename\core\tests\validator;

/**
 * base class for boolean validators
 */
class boolean extends \codename\core\tests\validator {

  /**
   * simple non-text value test
   * @return void
   */
  public function testValueNotABoolean() {
    $this->assertEquals('VALIDATION.VALUE_NOT_BOOLEAN', $this->getValidator()->validate(array())[0]['__CODE'] );
  }

}
