<?php

namespace codename\core\tests\validator\structure\config\crud;

use codename\core\exception;
use codename\core\tests\validator\structure;
use ReflectionException;

/**
 * I will test the pagination validator
 * @package codename\core
 * @since 2016-11-02
 */
class pagination extends structure
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueMissingArrKeys(): void
    {
        $errors = $this->getValidator()->validate([]);

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidLimit(): void
    {
        $config = [
          'limit' => 'AAA',
        ];
        static::assertEquals('VALIDATION.INVALID_LIMIT', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooSmall(): void
    {
        $config = [
          'limit' => -1,
        ];
        static::assertEquals('VALIDATION.LIMIT_TOO_SMALL', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooHigh(): void
    {
        $config = [
          'limit' => 1111111111111111111111111111111111111,
        ];
        static::assertEquals('VALIDATION.LIMIT_TOO_HIGH', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        $config = [
          'limit' => 10,
        ];
        static::assertEmpty($this->getValidator()->validate($config));
    }
}
