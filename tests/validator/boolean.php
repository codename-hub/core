<?php

namespace codename\core\tests\validator;

use codename\core\exception;
use codename\core\tests\validator;
use ReflectionException;

/**
 * base class for boolean validators
 */
class boolean extends validator
{
    /**
     * simple non-text value test
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueNotABoolean(): void
    {
        static::assertEquals('VALIDATION.VALUE_NOT_BOOLEAN', $this->getValidator()->validate([])[0]['__CODE']);
    }
}
