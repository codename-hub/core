<?php
namespace codename\core\tests\validator\boolean;

/**
 * base class for boolean validators
 */
class number extends \codename\core\tests\validator\boolean {

  /**
   * @return void
   */
  public function testValueIsNotBooleanNumber() {
    $this->assertEquals('VALIDATION.VALUE_NOT_NUMERIC_BOOLEAN', $this->getValidator()->validate(2)[0]['__CODE'] );
  }

  /**
   * @return void
   */
  public function testValueIsBooleanNumber() {
    $this->assertEquals(0, count($this->getValidator()->validate(1)) );
  }

  /**
   * @return void
   */
  public function testValueIsBoolean() {
    $this->assertEquals(0, count($this->getValidator()->validate(true)) );
  }

}
