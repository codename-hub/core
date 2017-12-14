<?php
namespace codename\core\tests\validator\number;

/**
 * base class for number validators
 */
class integer extends \codename\core\tests\validator\number {

  /**
   * @return void
   */
  public function testValueTooPrecise() {
    $this->assertEquals('VALIDATION.VALUE_TOO_PRECISE', $this->getValidator()->validate(1.2)[0]['__CODE'] );
  }

  /**
   * @return void
   */
  public function testValueIsInteger() {
    $this->assertEquals(0, count($this->getValidator()->validate(345)) );
  }

}
