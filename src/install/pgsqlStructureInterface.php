<?php
namespace codename\core\install;
use codename\core\install\dbStructureInterface;

/**
 * Defines an interface to access various db drivers for their structure/schema
 *
 * @author    Kevin Dargel
 */
class pgsqlStructureInterface extends sqlStructureInterface {
  /**
   * @inheritDoc
   */
  public function getDriverCompat(): string
  {
    return "pgsql";
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
  public function getTableStructure(string $connection, string $schema, string $table): array
  {
    // Constructing a temporary query component (consisting of the relevant pgsql attributes of a field/table)
    $arr = array();
    foreach(self::$pgSqlFieldAttributes as $k => $v) {
      $arr[] = implode(" as ", array($k,$v));
    }
    $select = implode(',', $arr);

    $db = $this->getDb($connection);
    $db->query(
      "SELECT	$select
      FROM   pg_attribute
      WHERE  attrelid = '$schema.$table'::regclass
      AND    NOT attisdropped
      AND    attnum > 0;"
    );
    $dbresult = $db->getResult();

    $result = array();

    foreach($dbresult as $r) {
      $t = new dbStructureElement();
      $t->connection = $connection;
      $t->schema = $schema;
      $t->table = $table;
      $t->column = $r['column'];
      $t->type = $r['type'];
      $t->notnull = $r['notnull'];
      $t->hasdefaultvalue = $r['hasdefaultvalue'];
      $result[] = $t;
    }

    return $result;
  }

  /**
   * @inheritDoc
   */
  public function getIsAutoincrement(string $connection, string $schema, string $table, string $column): bool
  {
    $db = $this->getDb($connection);
    $db->query(
      "SELECT pg_get_serial_sequence('$schema.$table','$column');"
    );
    return $db->getResult()[0] !== NULL;
  }

  /**
   * @inheritDoc
   */
  public function getPrimaryKeyExists(string $connection, string $schema, string $table, string $column): bool
  {
    $db = $this->getDb($connection);
    $db->query(
      "SELECT exists(SELECT 1
      FROM   pg_index i
      JOIN   pg_attribute a ON a.attrelid = i.indrelid
                           AND a.attnum = ANY(i.indkey)
      WHERE  i.indrelid = '$schema.$table'::regclass
      AND    a.attname = '$column'
      AND    i.indisprimary
      );"
    );
    return $db->getResult()[0][0];
  }

  /**
   * @inheritDoc
   */
  public function getUniqueKeyExists(string $connection, string $schema, string $table, string $column): bool
  {
    $db = $this->getDb($connection);
    $db->query(
      "SELECT exists(SELECT 1
      FROM   pg_index i
      JOIN   pg_attribute a ON a.attrelid = i.indrelid
                           AND a.attnum = ANY(i.indkey)
      WHERE  i.indrelid = '$schema.$table'::regclass
      AND    a.attname = '$column'
      AND    i.indisunique
      );"
    );
    return $db->getResult()[0][0];
  }


  protected $conversionTable = array(
      'text' => 'text',
      'text_timestamp' => 'timestamp without time zone',
      'text_date' => 'date',
      'number' => 'numeric', // was integer
      'number_natural' => 'integer',
      'boolean' => 'boolean',
      'structure' => 'text',
  );

}
