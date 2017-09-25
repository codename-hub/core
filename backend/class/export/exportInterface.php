<?php
namespace codename\core\export;

interface exportInterface {

    /**
     * This method exports the containing data to a file or whatever target format.
     * @return bool
     */
    public function export() : bool;

    /**
     * This method adds a field value-object to the fields in the export class.
     * @param \codename\core\value\text $field
     * @return \codename\core\export
     */
    public function addField(\codename\core\value\text $field) : \codename\core\export;

    /**
     * This method will add a row to the instance.
     * @return \codename\core\export
     */
    public function addRow(\codename\core\datacontainer $data) : \codename\core\export;

    /**
     * This implementation of addField adds multiple fields to the instance
     * @param array $fields
     * @return \codename\core\export
     */
    public function addFields(array $fields) : \codename\core\export;

    /**
     * This implementation of addRow adds multiple rows to the instance
     * @param array $rows
     * @return \codename\core\export
     */
    public function addRows(array $rows) : \codename\core\export;

}
