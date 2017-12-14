<?php
namespace codename\core\tests\validator\number;

/**
 * base class for number validators
 */
class money extends \codename\core\tests\validator\number {

  /**
   * @return void
   */
  public function testValueTooManyDigitsAfterComma() {
    $this->assertEquals('VALIDATION.TOO_MANY_DIGITS_AFTER_COMMA', $this->getValidator()->validate(1.222)[0]['__CODE'] );
  }

}
