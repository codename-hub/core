<?php
namespace codename\core\tests\validator\number;

/**
 * base class for number validators
 */
class port extends \codename\core\tests\validator\number {

  /**
   * @return void
   */
  public function testValueTooPrecise() {
    $this->assertEquals('VALIDATION.VALUE_TOO_PRECISE', $this->getValidator()->validate(15.123)[0]['__CODE'] );
  }

  /**
   * @return void
   */
  public function testValueIsSomePort() {
    $this->assertEquals(0, count($this->getValidator()->validate(3306)) );
  }

  /**
   * @return void
   */
  public function testValueTooSmall() {
    $this->assertEquals('VALIDATION.VALUE_TOO_SMALL', $this->getValidator()->validate(0)[0]['__CODE'] );
  }

  /**
   * @return void
   */
  public function testValueTooBig() {
    $this->assertEquals('VALIDATION.VALUE_TOO_BIG', $this->getValidator()->validate(65536)[0]['__CODE'] );
  }
  
}
