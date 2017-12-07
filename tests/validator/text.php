<?php
namespace codename\core\tests\validator;

/**
 * base class for text validators
 */
class text extends \codename\core\tests\validator {

  /**
   * simple non-text value test
   * @return void
   */
  public function testValueNotAString() {
    $this->assertEquals('VALIDATION.VALUE_NOT_A_STRING', $this->getValidator()->validate(array())[0]['__CODE'] );
  }

}
