<?php
namespace codename\core\tests\validator\text\color;

use \codename\core\app;

/**
 * I will test the rgba validator
 * @package codename\core
 * @since 2016-11-02
 */
class rgba extends \codename\core\tests\validator\text {

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
        $this->assertEquals('VALIDATION.VALUE_NOT_RGBA_STRING', $this->getValidator()->validate('rgba(rgba,100,100,0.9)')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEmpty($this->getValidator()->validate('rgba(100,100,100,0.9)'));
    }

}
