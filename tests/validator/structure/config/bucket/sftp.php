<?php

namespace codename\core\tests\validator\structure\config\bucket;

use codename\core\exception;
use codename\core\tests\validator\structure;
use ReflectionException;

/**
 * I will test the sftp validator
 * @package codename\core
 * @since 2016-11-02
 */
class sftp extends structure
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
          'sftpserver' => 'AAA',
        ];
        $errors = $this->getValidator()->validate($config);

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.PUBLIC_KEY_INVALID', $errors[0]['__CODE']);
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
          'sftpserver' => 'AAA',
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
    public function testTextInvalidKeySftpserver(): void
    {
        $config = [
          'public' => false,
          'basedir' => 'AAA',
          'sftpserver' => 'AAA',
        ];
        $errors = $this->getValidator()->validate($config);

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.SFTP_CONTAINER_INVALID', $errors[0]['__CODE']);
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
          'public' => false,
          'basedir' => 'AAA',
          'sftpserver' => [
            'host' => 'TODO?',
            'port' => 'TODO?',
            'user' => 'TODO?',
          ],
        ];
        static::assertEmpty($this->getValidator()->validate($config));
    }
}
