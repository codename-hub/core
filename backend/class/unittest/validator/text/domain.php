<?php
namespace codename\core\unittest\validator\text;

use \codename\core\app;

/**
 * I will test the domain validator
 * @package codename\core
 * @since 2016-11-03
 */
class domain extends \codename\core\unittest\validator\text {

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\unittest::testAll()
     */
    public function testAll() {
        $this->testValueNotString();
        $this->testValueTooLong();
        $this->testValueTooShort();
        $this->testValueInvalidchars();
        $this->testValueInvalidYear();
        $this->testValueInvalidMonth();
        $this->testValueInvalidDate();
        $this->testValueValid();
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueNotString() {
        $this->assertEquals('VALIDATION.VALUE_NOT_A_STRING', app::getValidator('text_date')->validate(array())[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooLong() {
        $this->assertEquals('VALIDATION.STRING_TOO_LONG', app::getValidator('text_date')->validate('111111111111111111111111111')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooShort() {
        $this->assertEquals('VALIDATION.STRING_TOO_SHORT', app::getValidator('text_date')->validate('11')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidchars() {
        $this->assertEquals('VALIDATION.INVALID_COUNT_AREAS', app::getValidator('text_date')->validate('1-1-1-1-1-')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidYear() {
        $this->assertEquals('VALIDATION.INVALID_YEAR', app::getValidator('text_date')->validate('19922-1-11')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidMonth() {
        $this->assertEquals('VALIDATION.INVALID_MONTH', app::getValidator('text_date')->validate('1992-222-1')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidDate() {
        $this->assertEquals('VALIDATION.INVALID_DATE', app::getValidator('text_date')->validate('1991-02-31')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEquals(array(), app::getValidator('text_date')->validate('1991-04-13'));
    }
    
}
