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
 * This is the CSV Exporter class
 * @package core
 * @since 2016-09-27
 */
class csv extends export implements exportInterface
{
    /**
     * This separator is used between each field
     * @var string
     */
    protected $separator_field = ", ";

    /**
     * this separator is used between each row
     * @var string
     */
    protected string $separator_row = '
';

    /**
     * Contains the file name
     * @var fileabsolute
     */
    protected fileabsolute $filename;

    /**
     * Contains the file's character encoding
     * @var string
     */
    protected $encoding = 'UTF-8';

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
        $_ret = '';
        foreach ($this->fields as $field) {
            $_ret .= $field->get();
            $_ret .= $this->separator_field;
        }
        $_ret .= $this->separator_row;

        foreach ($this->data as $row) {
            foreach ($this->fields as $field) {
                $_ret .= $row->getData($field->get());
                $_ret .= $this->separator_field;
            }
            $_ret .= $this->separator_row;
        }

        app::getFilesystem()->fileDelete($this->filename->get());
        app::getFilesystem()->fileWrite($this->filename->get(), mb_convert_encoding($_ret, $this->encoding, 'UTF-8'));

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
