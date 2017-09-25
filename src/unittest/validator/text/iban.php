<?php
namespace codename\core\unittest\validator\text;

use \codename\core\app;

/**
 * I will test the iban validator
 * @package codename\core
 * @since 2016-11-02
 */
class iban extends \codename\core\unittest\validator\text {

    /**
     * 
     * {@inheritDoc}
     * @see \codename\core\unittest::testAll()
     */
    public function testAll() {
        $this->testValueNotString();
        $this->testValueInvalidchars();
        $this->testValueCountryNotFound();
        $this->testValueLengthMismatch();
        $this->testValueChecksumMismatch();
        $this->testValueValid();
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueNotString() {
        $this->assertEquals('VALIDATION.VALUE_NOT_A_STRING', app::getValidator('text_iban')->validate(array())[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidchars() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', app::getValidator('text_iban')->validate('DE7953290000001042200.')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueCountryNotFound() {
        $this->assertEquals('VALIDATION.IBAN_COUNTRY_NOT_FOUND', app::getValidator('text_iban')->validate('XH13127953290000001042200')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueLengthMismatch() {
        $this->assertEquals('VALIDATION.IBAN_LENGH_NOT_MATCHING_COUNTRY', app::getValidator('text_iban')->validate('DE795329000000104220001')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueChecksumMismatch() {
        $this->assertEquals('VALIDATION.IBAN_CHECKSUM_FAILED', app::getValidator('text_iban')->validate('DE42532900000010422000')[0]['__CODE'] );
    }
    
    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEquals(array(), app::getValidator('text_iban')->validate('DE79532900000010422000'));
    }
    
}
