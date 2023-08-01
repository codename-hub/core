<?php

namespace codename\core\tests\validator\structure\config\crud;

use codename\core\exception;
use codename\core\tests\validator\structure;
use ReflectionException;

/**
 * I will test the action validator
 * @package codename\core
 * @since 2016-11-02
 */
class action extends structure
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
        static::assertCount(5, $errors);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[0]['__CODE']);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[1]['__CODE']);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[2]['__CODE']);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[3]['__CODE']);
        static::assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[4]['__CODE']);
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
          'name' => 'AAA',
          'view' => 'AAA',
          'context' => 'AAA',
          'icon' => 'AAA',
          'btnClass' => 'AAA',
          'pagination' => [
            'limit' => 10,
          ],
        ];
        static::assertEmpty($this->getValidator()->validate($config));
    }
}
