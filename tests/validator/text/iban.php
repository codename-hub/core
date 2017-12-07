<?php
namespace codename\core\tests\validator\text;

use \codename\core\app;

/**
 * I will test the iban validator
 * @package codename\core
 * @since 2016-11-02
 */
class iban extends \codename\core\tests\validator\text {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\unittest::testAll()
     */
    public function testAll() {
        parent::testAll();
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
    public function testValueInvalidchars() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('DE7953290000001042200.')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueCountryNotFound() {
        $this->assertEquals('VALIDATION.IBAN_COUNTRY_NOT_FOUND', $this->getValidator()->validate('XH13127953290000001042200')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueLengthMismatch() {
        $this->assertEquals('VALIDATION.IBAN_LENGH_NOT_MATCHING_COUNTRY', $this->getValidator()->validate('DE795329000000104220001')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueChecksumMismatch() {
        $this->assertEquals('VALIDATION.IBAN_CHECKSUM_FAILED', $this->getValidator()->validate('DE42532900000010422000')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEquals(array(), $this->getValidator()->validate('DE79532900000010422000'));
    }

}
