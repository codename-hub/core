<?php
namespace codename\core;

/**
 * This is the basic export class
 * @package core
 * @since 2016-09-27
 */
abstract class export implements \codename\core\export\exportInterface {

    /**
     * This contains the data rows.
     * @var \codename\core\datacontainer[]
     */
    protected $data = array();

    /**
     * This contains the field names.
     * @var \codename\core\value\text\modelfield[]
     */
    protected $fields = array();

}
