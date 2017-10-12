<?php
namespace codename\core\unittest\validator\text;

use \codename\core\app;

/**
 * I will test the hexadecimal color validator
 * @package codename\core
 * @since 2016-11-02
 */
class colorhexadecimal extends \codename\core\unittest\validator\text {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\unittest::testAll()
     */
    public function testAll() {
        parent::testAll();
        $this->testValueTooShort();
        $this->testValueTooLong();
        $this->testValueInvalidchars();
        $this->testValueValid();
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooShort() {
        $this->assertEquals('VALIDATION.STRING_TOO_SHORT', $this->getValidator()->validate('A')[0]['__CODE'] );
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
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('*AAAAAA')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEquals(array(), $this->getValidator()->validate('#FF0000'));
    }

}
