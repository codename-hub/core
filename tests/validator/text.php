<?php
namespace codename\core\tests\validator;

use codename\core\validator;

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

  /**
   * simple non-text value test
   * @return void
   */
  public function testValueIsNullNotAllowed() {
    $validator = new validator\text(false);
    $errors = $validator->validate(null);

    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('VALIDATION.VALUE_IS_NULL', $errors[0]['__CODE']);
  }

}
