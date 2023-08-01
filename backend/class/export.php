<?php

namespace codename\core;

use codename\core\export\exportInterface;
use codename\core\value\text\modelfield;

/**
 * This is the basic export class
 * @package core
 * @since 2016-09-27
 */
abstract class export implements exportInterface
{
    /**
     * This contains the data rows.
     * @var datacontainer[]
     */
    protected array $data = [];

    /**
     * This contains the field names.
     * @var modelfield[]
     */
    protected array $fields = [];
}
