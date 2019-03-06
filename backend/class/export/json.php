<?php
namespace codename\core\export;

use \codename\core\app;

/**
 * This is the JSON Exporter class
 * @package core
 * @since 2019-03-06
 */
class json extends \codename\core\export implements \codename\core\export\exportInterface {

    /**
     * Contains the file name
     * @var \codename\core\value\text\fileabsolute
     */
    protected $filename;

    /**
     * Contains the file's character encoding
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Using the custom generate-method, the file will be written to the given path
     * @param string $filename
     * @return \codename\core\export
     */
    public function setFilename(string $filename) : \codename\core\export {
        $this->filename = new \codename\core\value\text\fileabsolute($filename);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\export\exportInterface::export()
     */
    public function export() : bool {

        $data = [];
        foreach($this->data as $row) {
          $rowData = [];
          foreach($this->fields as $field) {
            $rowData[$field->get()] = $row->getData($field->get());
          }
          $data[] = $rowData;
        }

        $json = json_encode($data);

        app::getFilesystem()->fileDelete($this->filename->get());
        app::getFilesystem('local')->fileWrite($this->filename->get(), $json);

        return true;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\export\exportInterface::addField()
     */
    public function addField(\codename\core\value\text $field) : \codename\core\export {
        $this->fields[] = $field;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\export\exportInterface::addRow()
     */
    public function addRow(\codename\core\datacontainer $data) : \codename\core\export {
        $this->data[] = $data;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\export\exportInterface::addFields()
     */
    public function addFields(array $fields) : \codename\core\export {
        foreach($fields as $field) {
            $this->addField($field);
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\export\exportInterface::addRows()
     */
    public function addRows(array $rows) : \codename\core\export {
        foreach($rows as $row) {
            $this->addRow($row);
        }
        return $this;
    }

}
