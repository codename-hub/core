<?php

namespace codename\core\tests\validator\structure\config;

use codename\core\exception;
use codename\core\tests\validator\structure;
use ReflectionException;

/**
 * I will test the crud validator
 * @package codename\core
 * @since 2016-11-02
 */
class crud extends structure
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
        static::assertCount(3, $errors);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[0]['__CODE']);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[1]['__CODE']);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[2]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidPaginationLimit(): void
    {
        $config = [
          'pagination' => [
            'limit' => 'AAA',
          ],
          'visibleFields' => 'AAA',
          'order' => 'AAA',
        ];
        static::assertEquals('VALIDATION.PAGINATION_CONFIGURATION_INVALID', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValuePaginationLimitTooSmall(): void
    {
        $config = [
          'pagination' => [
            'limit' => -1,
          ],
          'visibleFields' => 'AAA',
          'order' => 'AAA',
        ];
        static::assertEquals('VALIDATION.PAGINATION_CONFIGURATION_INVALID', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValuePaginationLimitTooHigh(): void
    {
        $config = [
          'pagination' => [
            'limit' => 1111111111111111111111111111111111111,
          ],
          'visibleFields' => 'AAA',
          'order' => 'AAA',
        ];
        static::assertEquals('VALIDATION.PAGINATION_CONFIGURATION_INVALID', $this->getValidator()->validate($config)[0]['__CODE']);
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
          'pagination' => [
            'limit' => 10,
          ],
          'visibleFields' => 'AAA',
          'order' => 'AAA',
        ];
        static::assertEmpty($this->getValidator()->validate($config));
    }
}
