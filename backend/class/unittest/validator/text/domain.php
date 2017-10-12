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
        parent::testAll();
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
     * Testing validators for Errors
     * @return void
     */
    public function testValueHasNoDots() {
      $this->assertEquals('VALIDATION.NO_PERIOD_FOUND', $this->getValidator()->validate('blaah')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooLong() {
        // $this->assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate('111111111111111111111111111')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooShort() {
        // $this->assertEquals('VALIDATION.STRING_TOO_SHORT', $this->getValidator()->validate('11')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidchars() {
        $this->assertEquals('VALIDATION.INVALID_COUNT_AREAS', $this->getValidator()->validate('1-1-1-1-1-')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidYear() {
        $this->assertEquals('VALIDATION.INVALID_YEAR', $this->getValidator()->validate('19922-1-11')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidMonth() {
        $this->assertEquals('VALIDATION.INVALID_MONTH', $this->getValidator()->validate('1992-222-1')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidDate() {
        $this->assertEquals('VALIDATION.INVALID_DATE', $this->getValidator()->validate('1991-02-31')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEquals(array(), $this->getValidator()->validate('1991-04-13'));
    }

}
