<?php

namespace codename\core\value\text;

use codename\core\value\text;

class bic extends text
{
    /**
     * {@inheritDoc}
     * @see \codename\core\value::$validator
     */
    protected string $validator = 'text_bic';
}
