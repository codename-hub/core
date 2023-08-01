<?php

namespace codename\core\tests\validator\structure\api\codename;

use codename\core\exception;
use codename\core\tests\validator\structure;
use ReflectionException;

/**
 * I will test the response validator
 * @package codename\core
 * @since 2016-11-02
 */
class response extends structure
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
        static::assertCount(2, $errors);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[0]['__CODE']);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[1]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTextInvalidKeySuccess(): void
    {
        $config = [
          'success' => 'A',
          'data' => 'example',
        ];
        $errors = $this->getValidator()->validate($config);

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.INVALID_SUCCESS_IDENTIFIER', $errors[0]['__CODE']);
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
          'success' => 1,
          'data' => 'example',
        ];
        static::assertEmpty($this->getValidator()->validate($config));
    }
}
