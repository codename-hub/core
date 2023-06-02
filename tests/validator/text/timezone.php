<?php

namespace codename\core\tests\validator\text;

use codename\core\exception;
use codename\core\tests\validator\text;
use DateTimeZone;
use ReflectionException;

/**
 * I will test the text_timezone validator
 * @package codename\core
 * @since 2020-01-03
 */
class timezone extends text
{
    /**
     * Testing all available timezone identifiers
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testAllValidTimezoneIdentifiers(): void
    {
        $identifiers = DateTimeZone::listIdentifiers();
        $validator = $this->getValidator();

        //
        // NOTE: Different PHP versions may differ regarding their available timezones
        //
        foreach ($identifiers as $id) {
            $validator->reset();
            static::assertEmpty($res = $validator->validate($id), $id . print_r($res, true));
        }
    }

    /**
     * Testing an invalid timezone identifier on a foreign planet
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testInvalidTimezoneIdentifier(): void
    {
        static::assertEquals('VALIDATION.INVALID_TIMEZONE', $this->getValidator()->validate('Mars/Phobos')[0]['__CODE']);
    }

    /**
     * Testing a valid timezone offset
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValidTimezoneOffset(): void
    {
        static::assertEmpty($this->getValidator()->validate('+0200'));
    }

    /**
     * Testing a valid timezone offset
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValidTimezoneIdentifier(): void
    {
        static::assertEmpty($this->getValidator()->validate('Europe/Berlin'));
    }
}
