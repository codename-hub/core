<?php

namespace codename\core\tests\validator\number;

use codename\core\exception;
use codename\core\tests\validator\number;
use ReflectionException;

/**
 * base class for number validators
 */
class money extends number
{
    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueIsMoney(): void
    {
        static::assertCount(0, $this->getValidator()->validate(1.23));
    }
}
