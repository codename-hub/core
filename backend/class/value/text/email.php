<?php

namespace codename\core\value\text;

use codename\core\value\text;

class email extends text
{
    /**
     * {@inheritDoc}
     * @see \codename\core\value::$validator
     */
    protected string $validator = 'text_email';
}
