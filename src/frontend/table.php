<?php
namespace codename\core\frontend;

/**
 * table creation/generator class
 * @package core
 * @author Kevin Dargel
 * @since 2017-01-27
 */
class table extends element {

  /**
   * providing a nice default config
   */
  protected $defaultConfig = array(
    'table_class' => 'table table-normal responsive',
    'table_style' => '',
    'tr_class' => '',
    'tr_style' => '',
    'td_class' => '',
    'td_style' => ''
  );

  /**
   * if true, adds the <thead>/column captions
   * @var bool
   */
  public $showTableHeader = true;

  /**
   * @inheritDoc
   */
  public function __construct(array $configArray = array())
  {
    // providing default config.
    $configArray = array_merge($this->defaultConfig, $configArray);
    $value = parent::__construct($configArray);
    return $value;
  }

  /**
   * columns to be visible, also virtual/calculated columns
   */
  protected $columns = array();

  /**
   * add a normal column to be displayed that contains a key from the data row.
   */
  public function addColumn(string $columnName, string $columnKey) {
    $this->columns[$columnName] = $columnKey;
  }

  /**
   * add a virtual/calculated column with a callback/callable function
   */
  public function addVirtualColumn(string $columnName, callable $callback) {
    $this->columns[$columnName] = $callback;
  }

  /**
   * add multiple columns, also virtual ones in an assoc array (!)
   */
  public function addColumns(array $columnConfig) {
    foreach($columnConfig as $n => $k) {
      if(is_callable($k) && !is_string($k)) {
        $this->addVirtualColumn($n, $k);
      } else {
        $this->addColumn($n, $k);
      }
    }
  }

  /**
   * internal data storage array
   */
  protected $data = array();


  public function addRow(array $row) {
    $data[] = $row;
  }


  /**
   * use a specific dataset, additionally with a column config
   * provide an empty array as columnConfig to use NO default display columns
   */
  public function useDataset(array $dataset, $columnConfig = null) {
    if(is_array($columnConfig)) {
      foreach($columnConfig as $n => $k) {
        if(is_string($n) && is_string($k)) {
          $this->addColumn($n, $k); // name, key
        } else {
          $this->addColumn($k, $k); // key, key. "name" is an int index here.
        }
      }
    } else {
      foreach($dataset as $row) {
        foreach($row as $k => $v) {
          // if key exists in the defined array values (not keys!)
          if(!in_array($k, array_values($this->columns))) {
            $this->addColumn($k, $k); // use key as column name
          }
        }
      }
    }
    $this->data = $dataset;
  }

  /**
   * @inheritDoc
   */
  public function output(): string
  {
    \codename\core\app::getResponse()->requireResource('js', '/assets/plugins/jquery.bsmodal/jquery.bsmodal.js');
    \codename\core\app::getResponse()->requireResource('js', '/assets/plugins/jquery.bsmodal/jquery.bsmodal.init.js');

    $attributes = '';
    if($this->config->exists('table_attributes')) {
      foreach($this->config->get('table_attributes') as $key => $value) {
        $attributes .= " {$key}=\"{$value}\"";
      }
    }

    $html = "<table class=\"{$this->config->get('table_class')}\" style=\"{$this->config->get('table_style')}\" {$attributes}>";

    if($this->showTableHeader) {
      // add table header
      $html .= '<thead><tr>';
      foreach($this->columns as $name => $key) {
        $html .= "<td>{$name}</td>";
      }
      $html .= '</thead></tr>';
    }

    // add table body
    $html .= '<tbody>';
    foreach($this->data as $row) {
      $html .= '<tr>';
      foreach($this->columns as $colName => $colKey) {
        $html .= '<td>';
        if(is_callable($colKey) && !is_string($colKey)) {
          $html .= $colKey($row); // colKey is a callable
        } else {
          if(isset($row[$colKey])) {
            $html .= $row[$colKey];
          }
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
