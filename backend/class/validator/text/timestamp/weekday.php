<?php

namespace codename\core\validator\text\timestamp;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

class weekday extends text implements validatorInterface
{
    /**
     * Numeric representation of MONDAY, see: ISO-8601
     * @var int
     */
    public const MONDAY = 1;

    /**
     * Numeric representation of TUESDAY, see: ISO-8601
     * @var int
     */
    public const TUESDAY = 2;

    /**
     * Numeric representation of WEDNESDAY, see: ISO-8601
     * @var int
     */
    public const WEDNESDAY = 3;

    /**
     * Numeric representation of THURSDAY, see: ISO-8601
     * @var int
     */
    public const THURSDAY = 4;

    /**
     * Numeric representation of FRIDAY, see: ISO-8601
     * @var int
     */
    public const FRIDAY = 5;

    /**
     * Numeric representation of SATURDAY, see: ISO-8601
     * @var int
     */
    public const SATURDAY = 6;

    /**
     * Numeric representation of SUNDAY, see: ISO-8601
     * @var int
     */
    public const SUNDAY = 7;

    /**
     * array of allowed weekdays (ISO-8601)
     * @var int[]
     */
    protected array $allowedWeekdays = [];

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
     */
    public function __construct(bool $nullAllowed = false, array $allowedWeekdays = [])
    {
        $this->setAllowedWeekdays($allowedWeekdays);
        parent::__construct($nullAllowed, 1, 32, '0123456789 :-.');
        return $this;
    }

    /**
     * [setAllowedWeekdays description]
     * @param array $allowedWeekdays [description]
     */
    public function setAllowedWeekdays(array $allowedWeekdays = []): void
    {
        foreach ($allowedWeekdays as &$v) {
            $v = intval($v);
        }
        $this->allowedWeekdays = $allowedWeekdays;
        foreach ($this->allowedWeekdays as $d) {
            if (!in_array($d, [self::MONDAY, self::TUESDAY, self::WEDNESDAY, self::THURSDAY, self::FRIDAY, self::SATURDAY, self::SUNDAY])) {
                // error?
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): array
    {
        if (count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        if (count($this->allowedWeekdays) === 0) {
            $this->errorstack->addError('VALUE', 'ALLOWED_WEEKDAYS_NOT_SET', $this->allowedWeekdays);
        }

        if (!in_array(date('N', strtotime($value)), $this->allowedWeekdays)) {
            $this->errorstack->addError('VALUE', 'WEEKDAY_NOT_ALLOWED', $value);
        }

        return $this->errorstack->getErrors();
    }
}
