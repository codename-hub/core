<?php

namespace codename\core\validator;

use codename\core\validator;

/**
 * Validate numbers
 * @package core
 * @since 2016-02-04
 */
class number extends validator implements validatorInterface
{
    /**
     * I may hold a minimum value to validate against
     * @var float|int|null
     */
    private int|null|float $minvalue;

    /**
     * I may hold a maximum value for the number
     * @var float|int|null
     */
    private int|null|float $maxvalue;

    /**
     * I may hold the maximum amount of decimal places
     * @var null|int
     */
    private ?int $maxprecision;

    /**
     * Creates a numeric validator with the given option
     * @param bool $nullAllowed
     * @param float|null $minvalue [Used to check if the value is too low]
     * @param float|null $maxvalue [Used to check if the value is too high]
     * @param int|null $maxprecision [Used to check if the value has too many decimal places]
     */
    public function __construct(bool $nullAllowed = true, float $minvalue = null, float $maxvalue = null, int $maxprecision = null)
    {
        parent::__construct($nullAllowed);
        $this->minvalue = $minvalue;
        $this->maxvalue = $maxvalue;
        $this->maxprecision = $maxprecision;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate(mixed $value): array
    {
        parent::validate($value);

        if (!is_numeric($value)) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_A_NUMBER', $value);
            return $this->errorstack->getErrors();
        }

        if (!is_null($this->minvalue) && $value < $this->minvalue) {
            $this->errorstack->addError('VALUE', 'VALUE_TOO_SMALL', $value);
            return $this->errorstack->getErrors();
        }

        if (!is_null($this->maxvalue) && $value > $this->maxvalue) {
            $this->errorstack->addError('VALUE', 'VALUE_TOO_BIG', ['VAL' => $value, 'MAX' => $this->maxvalue]);
            return $this->errorstack->getErrors();
        }

        if (!is_null($this->maxprecision) && round($value, $this->maxprecision) != $value) {
            $this->errorstack->addError('VALUE', 'VALUE_TOO_PRECISE', $value);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }
}
