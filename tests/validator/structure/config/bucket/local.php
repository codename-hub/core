<?php

namespace codename\core\tests\validator\structure\config\bucket;

use codename\core\exception;
use codename\core\tests\validator\structure;
use ReflectionException;

/**
 * I will test the local validator
 * @package codename\core
 * @since 2016-11-02
 */
class local extends structure
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
    public function testTextInvalidKeyPublic(): void
    {
        $config = [
          'public' => 'AAA',
          'basedir' => 'AAA',
        ];
        $errors = $this->getValidator()->validate($config);

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.PUBLIC_KEY_NOT_FOUND', $errors[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTextInvalidKeyBaseurl(): void
    {
        $config = [
          'public' => true,
          'basedir' => 'AAA',
        ];
        $errors = $this->getValidator()->validate($config);

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.BASEURL_NOT_FOUND', $errors[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTextDirectoryNotFound(): void
    {
        static::markTestIncomplete('TODO: app::getFilesystem()');

        $config = [
          'public' => false,
          'basedir' => 'AAA',
        ];
        $errors = $this->getValidator()->validate($config);

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.DIRECTORY_NOT_FOUND', $errors[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::markTestIncomplete('TODO: app::getFilesystem()');

        $config = [
          'public' => false,
          'basedir' => 'AAA',
        ];
        static::assertEmpty($this->getValidator()->validate($config));
    }
}
