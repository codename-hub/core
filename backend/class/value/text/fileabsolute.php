<?php

namespace codename\core\value\text;

use codename\core\value\text;

class fileabsolute extends text
{
    /**
     * {@inheritDoc}
     * @see \codename\core\value::$validator
     */
    protected string $validator = 'text_filepath_absolute';
}
