<?php

namespace codename\core\tests\validator;

use codename\core\exception;
use codename\core\validator;
use ReflectionException;

/**
 * base class for text validators
 */
class text extends \codename\core\tests\validator
{
    /**
     * simple non-text value test
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueNotAString(): void
    {
        static::assertEquals('VALIDATION.VALUE_NOT_A_STRING', $this->getValidator()->validate([])[0]['__CODE']);
    }

    /**
     * simple non-text value test
     * @return void
     */
    public function testValueIsNullNotAllowed(): void
    {
        $validator = new validator\text(false);
        $errors = $validator->validate(null);

        static::assertNotEmpty($errors);
        static::assertCount(1, $errors);
        static::assertEquals('VALIDATION.VALUE_IS_NULL', $errors[0]['__CODE']);
    }
}
