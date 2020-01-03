<?php
namespace codename\core\tests\validator\text;

use \codename\core\app;

/**
 * I will test the email validator
 * @package codename\core
 * @since 2016-11-02
 */
class email extends \codename\core\tests\validator\text {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueNotString() {
        $this->assertEquals('VALIDATION.VALUE_NOT_A_STRING', $this->getValidator()->validate(array())[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooLong() {
        $this->assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidchars() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('*ASDASD')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueAtNotFound() {
        $this->assertEquals('VALIDATION.EMAIL_AT_NOT_FOUND', $this->getValidator()->validate('invalid')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueDomainInvalid() {
        $validationResult = $this->getValidator()->validate('invalid@');
        $this->assertEquals(
          in_array(
            $validationResult[0]['__CODE'],
            [
              'VALIDATION.EMAIL_DOMAIN_INVALID',
              'VALIDATION.EMAIL_INVALID',
            ]
          ),
          true
        );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueAtNotUnique() {
      $validationResult = $this->getValidator()->validate('invalid@sadas@as');
      $this->assertEquals(
        in_array(
          $validationResult[0]['__CODE'],
          [
            'VALIDATION.EMAIL_AT_NOT_UNIQUE',
            'VALIDATION.EMAIL_INVALID',
          ]
        ),
        true
      );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueDomainBlocked() {
        $this->assertEquals('VALIDATION.EMAIL_DOMAIN_BLOCKED', $this->getValidator()->validate('invalid@whyspam.me')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEquals(array(), $this->getValidator()->validate('mymail@example.com'));
    }

}
