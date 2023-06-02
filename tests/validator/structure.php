<?php

namespace codename\core\tests\validator;

use codename\core\exception;
use codename\core\validator;
use ReflectionException;

/**
 * base class for structure validators
 */
class structure extends \codename\core\tests\validator
{
    /**
     * simple non-text value test
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueNotAArray(): void
    {
        static::assertEquals('VALIDATION.VALUE_NOT_A_ARRAY', $this->getValidator()->validate('')[0]['__CODE']);
    }

    /**
     * simple non-text value test
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsNull(): void
    {
        static::assertEmpty($this->getValidator()->validate(null));
    }

    /**
     * simple non-text value test
     * @return void
     */
    public function testValueIsNullNotAllowed(): void
    {
        $validator = new validator\structure(false);
        $errors = $validator->validate(null);

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.VALUE_IS_NULL', $errors[0]['__CODE']);
    }

    /**
     * simple non-text value test
     * @return void
     */
    public function testValueIsValid(): void
    {
        $validator = new validator\structure();
        static::assertTrue($validator->isValid(null));
    }
}
