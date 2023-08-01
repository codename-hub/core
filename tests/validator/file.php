<?php

namespace codename\core\tests\validator;

use codename\core\exception;
use codename\core\tests\base;
use codename\core\validator;
use ReflectionException;

/**
 * base class for file validators
 */
class file extends base
{
    /**
     * simple non-text value test
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsNullNotAllowed(): void
    {
        $validator = new validator\file(false);
        $errors = $validator->validate(null);

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.VALUE_IS_NULL', $errors[0]['__CODE']);
    }

    /**
     * simple file not found test
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testFileNotFound(): void
    {
        $validator = new validator\file(false);
        $errors = $validator->validate(__DIR__ . '/file_not_found.txt');

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.FILE_NOT_FOUND', $errors[0]['__CODE']);
    }

    /**
     * simple forbidden mime type test
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testForbiddenMimeType(): void
    {
        $validator = new validator\file(false);
        $errors = $validator->validate(__DIR__ . '/file.txt');

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.FORBIDDEN_MIME_TYPE', $errors[0]['__CODE']);
    }

    /**
     * simple file not found test
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testFileValid(): void
    {
        $validator = new overrideableFile(false);
        $errors = $validator->validate(__DIR__ . '/file.txt');

        static::assertEmpty($errors);
    }

    /**
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     */
    protected function setUp(): void
    {
        $app = static::createApp();
        $app::getAppstack();

        static::setEnvironmentConfig([
          'test' => [
            'filesystem' => [
              'local' => [
                'driver' => 'local',
              ],
            ],
          ],
        ]);
    }

}

class overrideableFile extends validator\file
{
    protected array $mime_whitelist = [
      'text/plain'
    ];
}
