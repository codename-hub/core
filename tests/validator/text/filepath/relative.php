<?php

namespace codename\core\tests\validator\text\filepath;

use codename\core\exception;
use codename\core\tests\validator\text;
use ReflectionException;

/**
 * I will test the relative validator
 * @package codename\core
 * @since 2016-11-02
 */
class relative extends text
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueSetBeginSlash(): void
    {
        static::assertEquals('VALIDATION.MUST_NOT_BEGIN_WITH_SLASH', $this->getValidator()->validate('/example/example')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueSetEndSlash(): void
    {
        static::assertEquals('VALIDATION.MUST_NOT_END_WITH_SLASH', $this->getValidator()->validate('example/example/')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate('example/example'));
    }
}
