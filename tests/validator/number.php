<?php
namespace codename\core\tests\validator;

/**
 * base class for number validators
 */
class number extends \codename\core\tests\validator {

  /**
   * simple non-text value test
   * @return void
   */
  public function testValueNotANumber() {
    $this->assertEquals('VALIDATION.VALUE_NOT_A_NUMBER', $this->getValidator()->validate(array())[0]['__CODE'] );
  }

  /*
  public function testValueTooSmall() {
    $this->assertEquals('VALIDATION.VALUE_TOO_SMALL', $this->getValidator()->validate( insert too small number )[0]['__CODE'] );
  }

  */

}
