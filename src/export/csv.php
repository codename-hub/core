<?php
namespace codename\core\export;

use \codename\core\app;

/**
 * This is the CSV Exporter class
 * @package core
 * @since 2016-09-27
 */
class csv extends \codename\core\export implements \codename\core\export\exportInterface {

    /**
     * This separator is used between each field
     * @var string
     */
    protected $separator_field = ", ";

    /**
     * this separator is used between each row
     * @var string
     */
    protected $separator_row = '
';

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
        $_ret = '';
        foreach($this->fields as $field) {
            $_ret .= $field->get();
            $_ret .= $this->separator_field;
        }
        $_ret .= $this->separator_row;

        foreach($this->data as $row) {
            foreach($this->fields as $field) {
                $_ret .= $row->getData($field->get());
                $_ret .= $this->separator_field;
            }
            $_ret .= $this->separator_row;
        }
        
        app::getFilesystem()->fileDelete($this->filename->get());
        app::getFilesystem('local')->fileWrite($this->filename->get(), mb_convert_encoding($_ret, $this->encoding, 'UTF-8'));
        
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
