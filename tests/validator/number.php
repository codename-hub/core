<?php

namespace codename\core\tests\validator;

use codename\core\exception;
use codename\core\tests\validator;
use ReflectionException;

/**
 * base class for number validators
 */
class number extends validator
{
    /**
     * simple non-text value test
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueNotANumber(): void
    {
        static::assertEquals('VALIDATION.VALUE_NOT_A_NUMBER', $this->getValidator()->validate([])[0]['__CODE']);
    }

    /*
    public function testValueTooSmall() {
      static::assertEquals('VALIDATION.VALUE_TOO_SMALL', $this->getValidator()->validate( insert too small number )[0]['__CODE'] );
    }

    */
}
