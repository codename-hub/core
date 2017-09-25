<?php
namespace codename\core\value\text;

class email extends \codename\core\value\text {

    /**
     * {@inheritDoc}
     * @see \codename\core\value::$validator
     */
    protected $validator = 'text_email';
    
}
