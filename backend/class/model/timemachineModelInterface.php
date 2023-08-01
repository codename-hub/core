<?php

namespace codename\core\model;

/**
 * describes some very specific interface elements
 * that a timemachine delta storage model must have
 */
interface timemachineModelInterface
{
    /**
     * returns the name of the field
     * used for storing the model name
     *
     * @return string [fieldname]
     */
    public function getModelField(): string;

    /**
     * returns the name of the field
     * used for storing the reference (e.g. primary key value)
     *
     * @return string [fieldname]
     */
    public function getRefField(): string;

    /**
     * returns the name of the field
     * used for storing delta data
     *
     * @return string [fieldname]
     */
    public function getDataField(): string;
}
