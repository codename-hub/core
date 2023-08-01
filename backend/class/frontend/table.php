<?php

namespace codename\core\frontend;

use codename\core\app;
use codename\core\exception;
use codename\core\response\http;
use ReflectionException;

/**
 * table creation/generator class
 * @package core
 * @since 2017-01-27
 */
class table extends element
{
    /**
     * if true, adds the <thead>/column captions
     * @var bool
     */
    public bool $showTableHeader = true;
    /**
     * providing a nice default config
     */
    protected array $defaultConfig = [
      'table_class' => 'table table-normal responsive',
      'table_style' => '',
      'tr_class' => '',
      'tr_style' => '',
      'td_class' => '',
      'td_style' => '',
    ];
    /**
     * columns to be visible, also virtual/calculated columns
     */
    protected array $columns = [];
    /**
     * internal data storage array
     */
    protected array $data = [];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $configArray = [])
    {
        // providing default config.
        $configArray = array_merge($this->defaultConfig, $configArray);
        return parent::__construct($configArray);
    }

    /**
     * add multiple columns, also virtual ones in an assoc array (!)
     */
    public function addColumns(array $columnConfig): void
    {
        foreach ($columnConfig as $n => $k) {
            if (is_callable($k) && !is_string($k)) {
                $this->addVirtualColumn($n, $k);
            } else {
                $this->addColumn($n, $k);
            }
        }
    }

    /**
     * add a virtual/calculated column with a callback/callable function
     */
    public function addVirtualColumn(string $columnName, callable $callback): void
    {
        $this->columns[$columnName] = $callback;
    }

    /**
     * add a normal column to be displayed that contains a key from the data row.
     */
    public function addColumn(string $columnName, string $columnKey): void
    {
        $this->columns[$columnName] = $columnKey;
    }

    /**
     * @param array $row
     * @return void
     */
    public function addRow(array $row): void
    {
        $this->data[] = $row;
    }

    /**
     * use a specific dataset, additionally with a column config
     * provide an empty array as columnConfig to use NO default display columns
     */
    public function useDataset(array $dataset, $columnConfig = null): void
    {
        if (is_array($columnConfig)) {
            foreach ($columnConfig as $n => $k) {
                if (is_string($n) && is_string($k)) {
                    $this->addColumn($n, $k); // name, key
                } else {
                    $this->addColumn($k, $k); // key, key. "name" is an int index here.
                }
            }
        } else {
            foreach ($dataset as $row) {
                foreach ($row as $k => $v) {
                    // if key exists in the defined array values (not keys!)
                    if (!in_array($k, array_values($this->columns))) {
                        $this->addColumn($k, $k); // use key as column name
                    }
                }
            }
        }
        $this->data = $dataset;
    }

    /**
     * @return string
     * @throws ReflectionException
     * @throws exception
     */
    public function output(): string
    {
        $response = app::getResponse();
        if ($response instanceof http) {
            $response->requireResource('js', '/assets/plugins/jquery.bsmodal/jquery.bsmodal.js');
            $response->requireResource('js', '/assets/plugins/jquery.bsmodal/jquery.bsmodal.init.js');
        }

        $attributes = '';
        if ($this->config->exists('table_attributes')) {
            foreach ($this->config->get('table_attributes') as $key => $value) {
                $attributes .= " $key=\"$value\"";
            }
        }

        $html = "<table class=\"{$this->config->get('table_class')}\" style=\"{$this->config->get('table_style')}\" $attributes>";

        if ($this->showTableHeader) {
            // add table header
            $html .= '<thead><tr>';
            foreach ($this->columns as $name => $key) {
                $html .= "<td>$name</td>";
            }
            $html .= '</thead></tr>';
        }

        // add table body
        $html .= '<tbody>';
        foreach ($this->data as $row) {
            $html .= '<tr>';
            foreach ($this->columns as $colKey) {
                $html .= '<td>';
                if (is_callable($colKey) && !is_string($colKey)) {
                    $html .= $colKey($row); // colKey is a callable
                } elseif (isset($row[$colKey])) {
                    $html .= $row[$colKey];
                }
                $html .= '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        // close it.
        $html .= '</table>';

        return $html;
    }
}
