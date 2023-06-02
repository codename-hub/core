<?php

namespace codename\core\tests\validator\structure\config;

use codename\core\exception;
use codename\core\tests\validator\structure;
use ReflectionException;

/**
 * I will test the app validator
 * @package codename\core
 * @since 2016-11-02
 */
class app extends structure
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
    public function testValueInvalidContext(): void
    {
        $config = [
          'context' => [
            [],
          ],
          'defaultcontext' => '*123ABC',
          'defaulttemplate' => 'core',
        ];
        static::assertEquals('VALIDATION.KEY_CONTEXT_INVALID', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidDefaultcontext(): void
    {
        $config = [
          'context' => [
          ],
          'defaultcontext' => '*123ABC',
          'defaulttemplate' => 'core',
        ];
        static::assertEquals('VALIDATION.KEY_DEFAULTCONTEXT_INVALID', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidDefaulttemplate(): void
    {
        $config = [
          'context' => [
          ],
          'defaultcontext' => 'core',
          'defaulttemplate' => '*123ABC',
        ];
        static::assertEquals('VALIDATION.KEY_DEFAULTTEMPLATE_INVALID', $this->getValidator()->validate($config)[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueContextCustom(): void
    {
        $config = [
          'context' => [
            [
              'custom' => true,
            ],
          ],
          'defaultcontext' => 'core',
          'defaulttemplate' => 'core',
        ];
        static::assertEmpty($this->getValidator()->validate($config));
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
          'context' => [
          ],
          'defaultcontext' => 'core',
          'defaulttemplate' => 'core',
        ];
        static::assertEmpty($this->getValidator()->validate($config));
    }
}
