<?php
namespace codename\core\tests\validator\text;

use \codename\core\app;

/**
 * I will test the apploader validator
 * @package codename\core
 * @since 2016-11-02
 */
class apploader extends \codename\core\tests\validator\text {

    /**
     * apploader value must be lowercase
     * @return void
     */
    public function testTextApploaderValueStringMustBeLowercase() {
        $this->assertEquals('VALIDATION.VALUE_NOT_A_STRING', $this->getValidator()->validate(123)[0]['__CODE'] );
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueStringTooShort() {
        $this->assertEquals('VALIDATION.STRING_TOO_SHORT', $this->getValidator()->validate('a')[0]['__CODE'] );
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueStringTooLong() {
        $this->assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA')[0]['__CODE'] );
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueStringContainsInvalidCharacters() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('*ASDASD')[0]['__CODE'] );
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEmpty($this->getValidator()->validate('codename\\core'));
        return;
    }

}
