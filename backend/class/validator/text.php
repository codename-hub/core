<?php

namespace codename\core\validator;

use codename\core\validator;

/**
 * validate texts for length, (in)valid characters and regular expressions
 * @package core
 * @since 2016-02-04
 */
class text extends validator
{
    /**
     * What is the minimum length
     * @var int
     */
    protected int $minlength = 0;

    /**
     * What is the maximum length
     * @var int
     */
    protected int $maxlength = 0;

    /**
     * Contains allowed characters for the string
     * @var string
     */
    protected string $allowedchars = '';

    /**
     * Contains forbidden characters
     * @var string
     */
    protected string $forbiddenchars = '';

    /**
     * Contains preg_quoted allowed characters for the string
     * @var string
     */
    protected string $quotedAllowedchars = '';

    /**
     * Contains preg_quoted forbidden characters
     * @var string
     */
    protected string $quotedForbiddenchars = '';

    /**
     * @param bool $nullAllowed
     * @param int $minlength
     * @param int $maxlength
     * @param string $allowedchars
     * @param string $forbiddenchars
     */
    public function __construct(bool $nullAllowed = false, int $minlength = 0, int $maxlength = 0, string $allowedchars = '', string $forbiddenchars = '')
    {
        parent::__construct($nullAllowed);
        $this->minlength = $minlength;
        $this->maxlength = $maxlength;
        $this->allowedchars = $allowedchars;
        $this->forbiddenchars = $forbiddenchars;

        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate(mixed $value): array
    {
        if (count(parent::validate($value)) != 0) {
            return $this->getErrors();
        }

        if (!is_string($value)) {
            $this->errorstack->addError('VALUE', 'VALUE_NOT_A_STRING', $value);
            return $this->errorstack->getErrors();
        }

        if ($this->getMinlength() > 0 && strlen($value) < $this->getMinlength()) {
            $this->errorstack->addError('VALUE', 'STRING_TOO_SHORT', $value);
            return $this->errorstack->getErrors();
        }

        if ($this->getMaxlength() > 0 && strlen($value) > $this->getMaxlength()) {
            $this->errorstack->addError('VALUE', 'STRING_TOO_LONG', $value);
            return $this->errorstack->getErrors();
        }

        // search forbidden characters

        if (strlen($this->getAllowedchars()) > 0) {
            // match characters that are NOT in allowed chars
            if (preg_match('/[^' . $this->getQuotedAllowedchars() . ']/', $value, $matches) !== 0) {
                $this->errorstack->addError('VALUE', 'STRING_CONTAINS_INVALID_CHARACTERS', ['value' => $value, 'matches' => $matches]);
                return $this->errorstack->getErrors();
            }
        }

        if (strlen($this->getForbiddenchars()) > 0) {
            // match characters that are explicitly in forbidden chars
            if (preg_match('/[' . $this->getQuotedForbiddenchars() . ']/', $value, $matches) !== 0) {
                $this->errorstack->addError('VALUE', 'STRING_CONTAINS_INVALID_CHARACTERS', ['value' => $value, 'matches' => $matches]);
                return $this->errorstack->getErrors();
            }
        }

        return $this->errorstack->getErrors();
    }

    /**
     * Returns the minimum length property
     * @return int
     */
    protected function getMinlength(): int
    {
        return $this->minlength;
    }

    /**
     * Returns the max length property
     * @return int
     */
    protected function getMaxlength(): int
    {
        return $this->maxlength;
    }

    /**
     * Returns the allowed characters
     * @return string
     */
    protected function getAllowedchars(): string
    {
        return $this->allowedchars;
    }

    /**
     * Returns the preq_quoted allowed characters
     * @return string
     */
    protected function getQuotedAllowedchars(): string
    {
        if ($this->quotedAllowedchars == null) {
            $this->quotedAllowedchars = preg_quote($this->getAllowedchars(), '//');
        }
        return $this->quotedAllowedchars;
    }

    /**
     * Returns the forbidden characters
     * @return string
     */
    protected function getForbiddenchars(): string
    {
        return $this->forbiddenchars;
    }

    /**
     * Returns the preq_quoted forbidden characters
     * @return string
     */
    protected function getQuotedForbiddenchars(): string
    {
        if ($this->quotedForbiddenchars == null) {
            $this->quotedForbiddenchars = preg_quote($this->getForbiddenchars(), '//');
        }
        return $this->quotedForbiddenchars;
    }
}
