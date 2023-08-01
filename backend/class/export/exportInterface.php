<?php

namespace codename\core\export;

use codename\core\datacontainer;
use codename\core\export;
use codename\core\value\text;

interface exportInterface
{
    /**
     * This method exports the containing data to a file or whatever target format.
     * @return bool
     */
    public function export(): bool;

    /**
     * This method adds a field value-object to the fields in the export class.
     * @param text $field
     * @return export
     */
    public function addField(text $field): export;

    /**
     * This method will add a row to the instance.
     * @param datacontainer $data
     * @return export
     */
    public function addRow(datacontainer $data): export;

    /**
     * This implementation of addField adds multiple fields to the instance
     * @param array $fields
     * @return export
     */
    public function addFields(array $fields): export;

    /**
     * This implementation of addRow adds multiple rows to the instance
     * @param array $rows
     * @return export
     */
    public function addRows(array $rows): export;
}
