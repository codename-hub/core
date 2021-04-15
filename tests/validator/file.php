<?php
namespace codename\core\tests\validator;

use codename\core\validator;

/**
 * base class for file validators
 */
class file extends \codename\core\tests\validator {

  /**
   * simple non-text value test
   * @return void
   */
  public function testValueIsNullNotAllowed() {
    $validator = new validator\file(false);
    $errors = $validator->validate(null);

    $this->assertNotEmpty($errors);
    $this->assertCount(1, $errors);
    $this->assertEquals('VALIDATION.VALUE_IS_NULL', $errors[0]['__CODE']);
  }

}
