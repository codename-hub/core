<?php
namespace codename\core\unittest\validator\text;

use \codename\core\app;

/**
 * I will test the apploader validator
 * @package codename\core
 * @since 2016-11-02
 */
class apploader extends \codename\core\unittest\validator\text {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\unittest::testAll()
     */
    public function testAll() {
        $this->testValueNotAString();
        $this->testValueStringTooShort();
        $this->testValueStringTooLong();
        $this->testValueStringContainsInvalidCharacters();
        $this->testValueValid();
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueNotAString() {
        $this->assertEquals('VALIDATION.VALUE_NOT_A_STRING', app::getValidator('text_apploader')->validate(array())[0]['__CODE'] );
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueStringTooShort() {
        $this->assertEquals('VALIDATION.STRING_TOO_SHORT', app::getValidator('text_apploader')->validate('A')[0]['__CODE'] );
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueStringTooLong() {
        $this->assertEquals('VALIDATION.STRING_TOO_LONG', app::getValidator('text_apploader')->validate('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA')[0]['__CODE'] );
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueStringContainsInvalidCharacters() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', app::getValidator('text_apploader')->validate('*ASDASD')[0]['__CODE'] );
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEquals(array(), app::getValidator('text_apploader')->validate('codename\\core'));
        return;
    }

}
