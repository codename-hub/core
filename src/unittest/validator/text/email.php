<?php
namespace codename\core\unittest\validator\text;

use \codename\core\app;

/**
 * I will test the email validator
 * @package codename\core
 * @since 2016-11-02
 */
class email extends \codename\core\unittest\validator\text {

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\unittest::testAll()
     */
    public function testAll() {
        $this->testValueNotString();
        $this->testValueTooLong();
        $this->testValueInvalidchars();
        $this->testValueAtNotFound();
        $this->testValueDomainInvalid();
        $this->testValueAtNotUnique();
        $this->testValueDomainBlocked();
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueNotString() {
        $this->assertEquals('VALIDATION.VALUE_NOT_A_STRING', app::getValidator('text_email')->validate(array())[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueTooLong() {
        $this->assertEquals('VALIDATION.STRING_TOO_LONG', app::getValidator('text_email')->validate('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidchars() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', app::getValidator('text_email')->validate('*ASDASD')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueAtNotFound() {
        $this->assertEquals('VALIDATION.EMAIL_AT_NOT_FOUND', app::getValidator('text_email')->validate('invalid')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueDomainInvalid() {
        $this->assertEquals('VALIDATION.EMAIL_DOMAIN_INVALID', app::getValidator('text_email')->validate('invalid@')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueAtNotUnique() {
        $this->assertEquals('VALIDATION.EMAIL_AT_NOT_UNIQUE', app::getValidator('text_email')->validate('invalid@sadas@as')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueDomainBlocked() {
        $this->assertEquals('VALIDATION.EMAIL_DOMAIN_BLOCKED', app::getValidator('text_email')->validate('invalid@whyspam.me')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEquals(array(), app::getValidator('text_email')->validate('mymail@example.com'));
    }

}
