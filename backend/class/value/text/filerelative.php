<?php

namespace codename\core\value\text;

use codename\core\value\text;

class filerelative extends text
{
    /**
     * {@inheritDoc}
     * @see \codename\core\value::$validator
     */
    protected string $validator = 'text_filepath_relative';
}
