<?php
namespace codename\core\tests\validator\text;

use \codename\core\app;

/**
 * I will test the domain validator
 * @package codename\core
 * @since 2016-11-03
 */
class domain extends \codename\core\tests\validator\text {

    /**
     * Testing validators for Errors
     * @return void
     */
    public function testValueHasNoDots() {
      $this->assertEquals('VALIDATION.NO_PERIOD_FOUND', $this->getValidator()->validate('blaah')[0]['__CODE'] );
    }

    /**
     * [testInvalidDomain description]
     * @return [type] [description]
     */
    public function testValueIsUrl() {
        $this->assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('some-domain.com/blarp')[0]['__CODE'] );
    }

    /**
     * Testing validators for Errors
     * @return void
     */
    public function testValueTooLong() {
        // We're creating a 250+4 char string
        // breaking the default ASCII 253-char limit
        // this should be done correctly as we can only have 63 chars in a "label" e.g. <63chars>.<63chars>.com
        $this->assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate( str_repeat('k', 250).'.com' )[0]['__CODE'] );
    }

    /**
     * Testing validators for Errors
     * @return void
     */
    public function testValueTooShort() {
        $this->assertEquals('VALIDATION.STRING_TOO_SHORT', $this->getValidator()->validate('a.x')[0]['__CODE'] );
    }

    /**
     * Testing validators for Errors
     * @return void
     */
    public function testDomainResolves() {
        // @see: https://en.wikipedia.org/wiki/.invalid
        // @see: https://tools.ietf.org/html/rfc2606
        $this->assertEquals('VALIDATION.DOMAIN_NOT_RESOLVED', $this->getValidator()->validate('domain.invalid')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEmpty($this->getValidator()->validate('example.com'));
    }


}
