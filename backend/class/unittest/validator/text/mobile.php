<?php
namespace codename\core\unittest\validator\text;

use \codename\core\app;

/**
 * I will test the mobile validator
 * @package codename\core
 * @since 2016-11-02
 */
class mobile extends \codename\core\unittest\validator\text {

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\unittest::testAll()
     */
    public function testAll() {
        $this->testValueNotString();
        $this->testValueTooLong();
        $this->testValueInvalidchars();
        $this->testValueValid();
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueNotString() {
        $this->assertEquals('VALIDATION.VALUE_NOT_A_STRING', app::getValidator('text_mobile')->validate(array())[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooLong() {
        $this->assertEquals('VALIDATION.STRING_TOO_LONG', app::getValidator('text_mobile')->validate('+346759823475982347659234759234865923487562394875692384756923487659238476598237652398756329876')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidchars() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', app::getValidator('text_mobile')->validate('459"§!§345934')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEquals(array(), app::getValidator('text_mobile')->validate('+496622918818'));
    }
    
}
