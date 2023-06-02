<?php

namespace codename\core\tests\validator\text;

use codename\core\exception;
use codename\core\tests\validator\text;
use ReflectionException;

/**
 * I will test the json validator
 * @package codename\core
 * @since 2016-11-02
 */
class json extends text
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueEmptyString(): void
    {
        static::assertEmpty($this->getValidator()->validate(''));
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidJson(): void
    {
        static::assertEquals('VALIDATION.JSON_INVALID', $this->getValidator()->validate('AAAAA')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate('{"AAAAA":"AAAAA"}'));
    }
}
