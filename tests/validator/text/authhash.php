<?php

namespace codename\core\tests\validator\text;

use codename\core\exception;
use codename\core\tests\validator\text;
use ReflectionException;

/**
 * I will test the authhash validator
 * @package codename\core
 * @since 2016-11-02
 */
class authhash extends text
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooShort(): void
    {
        static::assertEquals('VALIDATION.STRING_TOO_SHORT', $this->getValidator()->validate('A')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueTooLong(): void
    {
        static::assertEquals('VALIDATION.STRING_TOO_LONG', $this->getValidator()->validate('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueInvalidchars(): void
    {
        static::assertEquals('VALIDATION.STRING_CONTAINS_INVALID_CHARACTERS', $this->getValidator()->validate('qyDdMHWwcDuC2VZwgKuG3RfDTkZcqzB92EPCHkQvKhpBvFa3QCWzKQ724AmfPMgJ4SLApc5fKvMEkFnS3rXGaYbdkP2F2sZbZXn8pqbBPVMARtnVEzgvzRua6de62An$')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate('qyDdMHWwcDuC2VZwgKuG3RfDTkZcqzB92EPCHkQvKhpBvFa3QCWzKQ724AmfPMgJ4SLApc5fKvMEkFnS3rXGaYbdkP2F2sZbZXn8pqbBPVMARtnVEzgvzRua6de62Anz'));
    }
}
