<?php

namespace codename\core\tests\validator\structure;

use codename\core\exception;
use codename\core\tests\validator\structure;
use ReflectionException;

/**
 * I will test the cart validator
 * @package codename\core
 * @since 2016-11-02
 */
class cart extends structure
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTextInvalidProduct(): void
    {
        static::assertEquals('VALIDATION.INVALID_PRODUCT_FOUND', $this->getValidator()->validate([[]])[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate([]));
    }
}
