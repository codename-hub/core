<?php

namespace codename\core\tests\validator\structure;

use codename\core\exception;
use codename\core\tests\validator\structure;
use ReflectionException;

/**
 * I will test the appstack validator
 * @package codename\core
 * @since 2016-11-02
 */
class appstack extends structure
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTextAppstackEmpty(): void
    {
        static::assertEquals('VALIDATION.APPSTACK_EMPTY', $this->getValidator()->validate([])[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate(['core']));
    }
}
