<?php

namespace codename\core\tests\validator\text\timestamp;

use codename\core\exception;
use codename\core\tests\validator\text;
use codename\core\validator;
use ReflectionException;

/**
 * I will test the weekday validator
 * @package codename\core
 * @since 2016-11-02
 */
class weekday extends text
{
    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueAllowedWeekdaysNotSet(): void
    {
        static::assertEquals('VALIDATION.ALLOWED_WEEKDAYS_NOT_SET', $this->getValidator(false)->validate('2021-04-17')[0]['__CODE']);
    }

    /**
     * {@inheritDoc}
     */
    public function getValidator($allWeekdays = true): validator
    {
        $weekdays = [];
        if ($allWeekdays) {
            $weekdays[] = validator\text\timestamp\weekday::MONDAY;
            $weekdays[] = validator\text\timestamp\weekday::TUESDAY;
            $weekdays[] = validator\text\timestamp\weekday::WEDNESDAY;
            $weekdays[] = validator\text\timestamp\weekday::THURSDAY;
            $weekdays[] = validator\text\timestamp\weekday::FRIDAY;
        }
        return new validator\text\timestamp\weekday(false, $weekdays);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueNotAllowed(): void
    {
        static::assertEquals('VALIDATION.WEEKDAY_NOT_ALLOWED', $this->getValidator()->validate('2021-04-17')[0]['__CODE']);
    }

    /**
     * Testing validators for Errors
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    public function testValueValid(): void
    {
        static::assertEmpty($this->getValidator()->validate('2021-04-13'));
    }
}
