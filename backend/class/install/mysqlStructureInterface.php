<?php
namespace codename\core\install;
use codename\core\install\dbStructureInterface;

/**
 * Defines an interface to access various db drivers for their structure/schema
 *
 * @author    Kevin Dargel
 */
class mysqlStructureInterface extends sqlStructureInterface {
  /**
   * @inheritDoc
   */
  public function getDriverCompat(): string
  {
    return "mysql";
  }

  /**
   * PostgreSQL-specific attribute query components
   * for schema/structure
   * @var array
   */
  protected static $pgSqlFieldAttributes = array(
      'attname' => 'column',
      'format_type(atttypid, atttypmod)' => 'type',
      'attnotnull' => 'notnull',
      'atthasdef' => 'hasdefaultvalue'
  );

  /**
   * @inheritDoc
   */
  public function getAttributes($connection, $schema, $table): array
  {
    return array();
    // see https://www.postgresql.org/docs/8.3/static/catalog-pg-attribute.html
    $arr = array();
    foreach($this->postgresqlatt as $k => $v) {

      $arr[] = implode(" as ", array($k,$v));
    }
    $select = implode(',', $arr);

    $db = app::getDb($connection);
    $db->query(
      "SELECT	$select
      FROM   pg_attribute
      WHERE  attrelid = '$schema.$table'::regclass
      AND    NOT attisdropped
      AND    attnum > 0;"
      // ORDER  BY attnum;"
    );
    return $db->getResult();
  }

  /**
   * @inheritDoc
   */
  public function modifyColumn(dbStructureElement $element): bool
  {
    // create schema, if needed
    if(!$this->getSchemaExists($element->connection,$element->schema)) {
        $this->createSchema($element->connection,$element->schema);
    }

    // create table in schema, if needed
    if(!$this->getTableExists($element->connection,$element->schema,$element->table)) {
        $this->createTable($element->connection,$element->schema,$element->table);
    }

    $db = $this->getDb($element->connection);

    // IF COLUMN EXISTS (and we're changing the type! (TODO))
    if($this->getColumnExists($element->connection,$element->schema,$element->table,$element->column)) {
      // move the existing table/ReflectionParameter
      $db->query(
        "ALTER TABLE $element->schema.$element->table MODIFY $element->column $element->dbdoc_data;"
      );
    }
    // $dbresult = $db->getResult(); ?
    return true; // TODO implement error or none?


  }

  /**
   * @inheritDoc
   */
  public function getTableStructure(string $connection, string $schema, string $table): array
  {
    // Constructing a temporary query component (consisting of the relevant pgsql attributes of a field/table)

    $db = $this->getDb($connection);

    $db->query(
      "DESCRIBE $schema.$table;"
    );
    $dbresult = $db->getResult();

    // query some more information
    $db->query(
      "SELECT column_name, column_type, data_type FROM information_schema.columns WHERE table_schema = '$schema' AND table_name = '$table';"
    );
    $datatypeResult = $db->getResult();

    $result = array();

    foreach($dbresult as $r) {

      $datatype = '';
      foreach($datatypeResult as $dt) {
        if($dt['column_name'] == $r['Field']) {
          $datatype = $dt['data_type'];
        }
      }

      $t = new dbStructureElement();
      $t->connection = $connection;
      $t->schema = $schema;
      $t->table = $table;
      $t->column = $r['Field'];
      $t->type = $datatype; // $r['Type']; // for MySQL we're using the underlying datatype instead of the exact column type
      $t->notnull = !$r['Null'];
      $t->hasdefaultvalue = $r['Default'];
      $result[] = $t;
    }
    // var_export($result);
    return $result;
  }

  /**
   * @inheritDoc
   */
  public function getIsAutoincrement(string $connection, string $schema, string $table, string $column): bool
  {
    $db = $this->getDb($connection);
    /*$db->query(
      "SELECT pg_get_serial_sequence('$schema.$table','$column');"
    );
    return $db->getResult()[0] !== NULL;*/
    $db->query(
      "SELECT exists(SELECT 1
        FROM information_schema.columns
        WHERE table_schema = '$schema'
        AND table_name = '$table'
        AND column_name = '$column'
        AND EXTRA like '%auto_increment%'
      ) as result;"
    );
    return $db->getResult()[0]['result'];
  }

  /**
   * @inheritDoc
   */
  public function getPrimaryKeyExists(string $connection, string $schema, string $table, string $column): bool
  {
    $db = $this->getDb($connection);
    $db->query(
      "SELECT exists(SELECT 1
      FROM   information_schema.columns
      WHERE  table_schema = '$schema'
      AND table_name = '$table'
      AND column_name = '$column'
      AND column_key = 'PRI'
      ) as result;"
    );
    return $db->getResult()[0]['result'];
  }

  /**
   * @inheritDoc
   */
  public function getForeignKeyExists(
    string $connection,
    string $schema,
    string $table,
    string $column,
    string $ref_schema,
    string $ref_table,
    string $ref_column
  ): bool {
    return false;
    $db = $this->getDb($connection);
    $db->query(
      "SELECT exists(SELECT 1
      FROM information_schema.table_constraints tc
      INNER JOIN information_schema.key_column_usage kcu
      USING (constraint_catalog, constraint_schema, constraint_name)
      WHERE constraint_type = 'FOREIGN KEY'
      AND tc.table_name = '$ref_table'
      AND tc.table_schema = '$ref_schema'
      AND tc.column_name = '$ref_column'
      AND tc.table_schema = '$schema'
      AND tc.table_name = '$table'
      AND kcu.column_name = '$column'
      );"
    );
    return $db->getResult()[0][0];

  }

  /**
   * @inheritDoc
   */
  public function getUniqueKeyExists(string $connection, string $schema, string $table, string $column): bool
  {
    return false;
    $db = $this->getDb($connection);
    $db->query(
      "SELECT exists(SELECT 1
      FROM   pg_index i
      JOIN   pg_attribute a ON a.attrelid = i.indrelid
                           AND a.attnum = ANY(i.indkey)
      WHERE  i.indrelid = '$schema.$table'::regclass
      AND    a.attname = '$column'
      AND    i.indisunique
      ) as result;"
    );
    return $db->getResult()[0]['result'];
  }

  /**
   * @inheritDoc
   */
  public function createColumn(dbStructureElement $element): bool
  {
    // create schema, if needed
    if(!$this->getSchemaExists($element->connection,$element->schema)) {
        $this->createSchema($element->connection,$element->schema);
    }

    // create table in schema, if needed
    if(!$this->getTableExists($element->connection,$element->schema,$element->table)) {
        // SPECIAL for MySQL here:
        // we cannot create an EMPTY table, therefore, create it with the column this function has been called with
        $this->createTable($element->connection,$element->schema,$element->table, $element);
        return true;
    }

    $db = $this->getDb($element->connection);


    $db->query(
       "ALTER TABLE $element->schema.$element->table ADD COLUMN $element->column $element->dbdoc_data;"
    );
    // $dbresult = $db->getResult(); ?
    return true; // TODO implement error or none?

  }

  /**
   * @inheritDoc
   */
  protected function createTable(string $connection, string $schema, string $table, $element = null): bool
  {
    if($element != null && $element instanceof dbStructureElement) {
      $db = $this->getDb($connection);
      $db->query(
        "CREATE TABLE $schema.$table (
          $element->column $element->dbdoc_data
        );"
      );
      return true; // TODO implement error or none?
    } else {
      return false;
    }
  }



  protected $conversionTable = array(
      'text' => array('text', 'mediumtext'),
      'text_timestamp' => 'datetime',
      'text_date' => 'date',
      'number' => 'numeric', // was integer
      'number_natural' => array('integer', 'int', 'bigint'),
      'boolean' => 'boolean',
      'structure' => array('text', 'mediumtext'),
      'mixed' => array('text')
  );



}
