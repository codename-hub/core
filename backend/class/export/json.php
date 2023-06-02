<?php

namespace codename\core\export;

use codename\core\app;
use codename\core\datacontainer;
use codename\core\exception;
use codename\core\export;
use codename\core\value\text;
use codename\core\value\text\fileabsolute;
use ReflectionException;

/**
 * This is the JSON Exporter class
 * @package core
 * @since 2019-03-06
 */
class json extends export implements exportInterface
{
    /**
     * Contains the file name
     * @var fileabsolute
     */
    protected fileabsolute $filename;

    /**
     * Contains the file's character encoding
     * @var string
     */
    protected string $encoding = 'UTF-8';

    /**
     * Using the custom generate-method, the file will be written to the given path
     * @param string $filename
     * @return export
     * @throws ReflectionException
     * @throws exception
     */
    public function setFilename(string $filename): export
    {
        $this->filename = new fileabsolute($filename);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @return bool
     * @throws ReflectionException
     * @throws exception
     * @see exportInterface::export
     */
    public function export(): bool
    {
        $data = [];
        foreach ($this->data as $row) {
            $rowData = [];
            foreach ($this->fields as $field) {
                $rowData[$field->get()] = $row->getData($field->get());
            }
            $data[] = $rowData;
        }

        $json = json_encode($data);

        app::getFilesystem()->fileDelete($this->filename->get());
        app::getFilesystem()->fileWrite($this->filename->get(), $json);

        return true;
    }

    /**
     *
     * {@inheritDoc}
     * @see exportInterface::addFields
     */
    public function addFields(array $fields): export
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see exportInterface::addField
     */
    public function addField(text $field): export
    {
        $this->fields[] = $field;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see exportInterface::addRows
     */
    public function addRows(array $rows): export
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see exportInterface::addRow
     */
    public function addRow(datacontainer $data): export
    {
        $this->data[] = $data;
        return $this;
    }
}
