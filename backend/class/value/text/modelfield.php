<?php
namespace codename\core\value\text;

class modelfield extends \codename\core\value\text {

    /**
     * {@inheritDoc}
     * @see \codename\core\value::$validator
     */
    protected $validator = 'text_modelfield';

    /**
     * @inheritDoc
     */
    public function __construct($value)
    {
      parent::__construct($value);
      $exp = explode('.', $value);
      if(sizeof($exp) == 1) {
        $this->field = $exp[0];
      } elseif(sizeof($exp) == 2) {
        $this->table = $exp[0];
        $this->field = $exp[1];
      } elseif(sizeof($exp) == 3) {
        $this->schema = $exp[0];
        $this->table = $exp[1];
        $this->field = $exp[2];
      } else {
        // throw exception
      }
      return $this;
    }

    /**
     * creates a new text_modelfield_virtual value object
     * @param  string                                 $field [description]
     * @return \codename\core\value\text\modelfield          [description]
     */
    public static function getInstance(string $field) : \codename\core\value\text\modelfield {
      if(isset(self::$cached[$field])) {
        return self::$cached[$field];
      } else {
        self::$cached[$field] = new self($field);
        return self::$cached[$field];
      }
    }

    /**
     * @var modelfield[]
     */
    protected static $cached = array();

    
    protected $field = null;

    /**
     * @inheritDoc
     */
    public function get()
    {
      return $this->field;
    }

    protected $table = null;

    public function getTable() {
      return $this->table;
    }

    protected $schema = null;

    public function getSchema() {
      return $this->schema;
    }

    public function getValue() {
      return $this->value;
    }

}
