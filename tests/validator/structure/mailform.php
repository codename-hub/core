<?php

namespace codename\core\tests\validator\structure;

use codename\core\exception;
use codename\core\tests\validator\structure;
use ReflectionException;

/**
 * I will test the mailform validator
 * @package codename\core
 * @since 2016-11-02
 */
class mailform extends structure
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
    public function testTextInvalidEmailRecipient(): void
    {
        $config = [
          'recipient' => '.@example.com',
          'subject' => 'example',
          'body' => 'example',
        ];
        $errors = $this->getValidator()->validate($config);

        static::assertNotEmpty($errors);
        static::assertCount(2, $errors);
        static::assertEquals('VALIDATION.EMAIL_INVALID', $errors[0]['__CODE']);
        static::assertEquals('VALIDATION.INVALID_EMAIL_ADDRESS', $errors[1]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTextInvalidEmailCc(): void
    {
        $config = [
          'recipient' => 'example@example.com',
          'cc' => '.@example.com',
          'subject' => 'example',
          'body' => 'example',
        ];
        $errors = $this->getValidator()->validate($config);

        static::assertNotEmpty($errors);
        static::assertCount(2, $errors);
        static::assertEquals('VALIDATION.EMAIL_INVALID', $errors[0]['__CODE']);
        static::assertEquals('VALIDATION.INVALID_EMAIL_ADDRESS', $errors[1]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTextInvalidEmailBcc(): void
    {
        $config = [
          'recipient' => 'example@example.com',
          'bcc' => '.@example.com',
          'subject' => 'example',
          'body' => 'example',
        ];
        $errors = $this->getValidator()->validate($config);

        static::assertNotEmpty($errors);
        static::assertCount(2, $errors);
        static::assertEquals('VALIDATION.EMAIL_INVALID', $errors[0]['__CODE']);
        static::assertEquals('VALIDATION.INVALID_EMAIL_ADDRESS', $errors[1]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTextInvalidEmailReplyTo(): void
    {
        $config = [
          'recipient' => 'example@example.com',
          'reply-to' => '.@example.com',
          'subject' => 'example',
          'body' => 'example',
        ];
        $errors = $this->getValidator()->validate($config);

        static::assertNotEmpty($errors);
        static::assertCount(2, $errors);
        static::assertEquals('VALIDATION.EMAIL_INVALID', $errors[0]['__CODE']);
        static::assertEquals('VALIDATION.INVALID_EMAIL_ADDRESS', $errors[1]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTextInvalidSubject(): void
    {
        $config = [
          'recipient' => 'example@example.com',
          'subject' => '',
          'body' => 'example',
        ];
        $errors = $this->getValidator()->validate($config);

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.INVALID_EMAIL_SUBJECT', $errors[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testTextInvalidBody(): void
    {
        $config = [
          'recipient' => 'example@example.com',
          'subject' => 'example',
          'body' => '',
        ];
        $errors = $this->getValidator()->validate($config);

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.INVALID_EMAIL_BODY', $errors[0]['__CODE']);
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
          'recipient' => 'example@example.com',
          'subject' => 'example',
          'body' => 'example',
        ];
        static::assertEmpty($this->getValidator()->validate($config));
    }
}
