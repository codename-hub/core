<?php

namespace codename\core\tests\validator\text;

use codename\core\exception;
use codename\core\tests\validator\text;
use ReflectionException;

/**
 * I will test the domain validator
 * @package codename\core
 * @since 2016-11-03
 */
class domain extends text
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueHasNoDots(): void
    {
        static::assertEquals('VALIDATION.NO_PERIOD_FOUND', $this->getValidator()->validate('blaah')[0]['__CODE']);
    }

    /**
     * [testInvalidDomain description]
     * @return void [type] [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsUrl(): void
    {
        static::assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('some-domain.com/blarp')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooLong(): void
    {
        // We're creating a 250+4 char string
        // breaking the default ASCII 253-char limit
        // this should be done correctly as we can only have 63 chars in a "label" e.g. <63chars>.<63chars>.com
        static::assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate(str_repeat('k', 250) . '.com')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooShort(): void
    {
        static::assertEquals('VALIDATION.STRING_TOO_SHORT', $this->getValidator()->validate('a.x')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testDomainResolves(): void
    {
        // @see: https://en.wikipedia.org/wiki/.invalid
        // @see: https://tools.ietf.org/html/rfc2606
        static::assertEquals('VALIDATION.DOMAIN_NOT_RESOLVED', $this->getValidator()->validate('domain.invalid')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate('example.com'));
    }
}
