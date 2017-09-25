<?php
namespace codename\core\export\csv;

/**
 * This is the CSV Exporter class
 * @package core
 * @since 2016-09-27
 */
class excel extends \codename\core\export\csv implements \codename\core\export\exportInterface {

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
