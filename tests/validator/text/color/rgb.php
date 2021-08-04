<?php
namespace codename\core\tests\validator\text\color;

use \codename\core\app;

/**
 * I will test the rgb validator
 * @package codename\core
 * @since 2016-11-02
 */
class rgb extends \codename\core\tests\validator\text {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooShort() {
        $this->assertEquals('VALIDATION.STRING_TOO_SHORT', $this->getValidator()->validate('1')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooLong() {
        $this->assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate('111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidchars() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('*ASDASD123456')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidRgbString() {
        $this->assertEquals('VALIDATION.VALUE_NOT_RGB_STRING', $this->getValidator()->validate('rgb(rgb, 255, 0)')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEmpty($this->getValidator()->validate('rgb(223, 255, 0)'));
    }

}
