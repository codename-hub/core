<?php

namespace codename\core\export\csv;

use codename\core\export\csv;
use codename\core\export\exportInterface;

/**
 * This is the CSV Exporter class
 * @package core
 * @since 2016-09-27
 */
class excel extends csv implements exportInterface
{
    /**
     *
     * @var string
     */
    protected $separator_field = ';';

    /**
     *
     * @var string
     */
    protected $encoding = 'Windows-1252';
}
