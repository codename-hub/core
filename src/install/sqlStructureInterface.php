<?php
namespace codename\core\install;
use codename\core\install\dbStructureInterface;

/**
 * Defines an interface to access various db drivers for their structure/schema
 *
 * @author    Kevin Dargel
 */
abstract class sqlStructureInterface extends dbStructureInterface {

  /**
   * @inheritDoc
   */
  public function getSchemaExists($connection, $schema): bool
  {
    $db = $this->getDb($connection);
    $db->query(
        "SELECT exists(select 1 FROM information_schema.schemata WHERE schema_name = '$schema') as result;"
    );
    return $db->getResult()[0]['result']; // Alternatively, access the key 'exists'
  }

  /**
   * @inheritDoc
   */
  public function getTableExists($connection, $schema, $table): bool
  {
    $db = $this->getDb($connection);
    $db->query(
        "SELECT exists(select 1 FROM information_schema.tables WHERE table_schema = '$schema' AND table_name = '$table') as result;"
    );
    return $db->getResult()[0]['result'];
  }

  /**
   * @inheritDoc
   */
  public function getColumnExists($connection, $schema, $table, $column): bool
  {
    $db = $this->getDb($connection);
    $db->query(
        "SELECT exists(select 1 FROM information_schema.columns WHERE table_schema = '$schema' AND table_name = '$table' AND column_name = '$column') as result;"
    );
    return $db->getResult()[0]['result'];
  }

  /**
   * @inheritDoc
   */
  protected function createSchema(string $connection, string $schema): bool
  {
    $db = $this->getDb($connection);
    $db->query(
      "CREATE SCHEMA $schema;"
    );
    // $dbresult = $db->getResult(); ?
    return true; // TODO implement error or none?
  }

  /**
   * @inheritDoc
   */
  protected function createTable(string $connection, string $schema, string $table, $element = null): bool
  {
    $db = $this->getDb($connection);
    $db->query(
      "CREATE TABLE $schema.$table ();"
    );
    return true; // TODO implement error or none?
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
        $this->createTable($element->connection,$element->schema,$element->table);
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
  public function replaceColumn(dbStructureElement $element): bool
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
         "ALTER TABLE $element->schema.$element->table RENAME COLUMN $element->column TO ".$element->column."_OLD;"
      );
    }

    // create a new column
    $this->createColumn($element);

    // $db->query(
    //    "ALTER TABLE $element->schema.$element->table ALTER COLUMN $element->column TYPE $element->dbdoc_data;"
    // );
    // $dbresult = $db->getResult(); ?
    return true; // TODO implement error or none?

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
        "ALTER TABLE $element->schema.$element->table ALTER COLUMN $element->column TYPE $element->dbdoc_data;"
      );
    }
    // $dbresult = $db->getResult(); ?
    return true; // TODO implement error or none?

  }


  /**
   * @inheritDoc
   */
  public function getForeignKeyExists(string $connection, string $schema, string $table, string $column, string $ref_schema, string $ref_table, string $ref_column): bool
  {
    $db = $this->getDb($connection);
    $db->query(
      "SELECT exists(SELECT 1
      FROM information_schema.table_constraints tc
      INNER JOIN information_schema.constraint_column_usage ccu
      USING (constraint_catalog, constraint_schema, constraint_name)
      INNER JOIN information_schema.key_column_usage kcu
      USING (constraint_catalog, constraint_schema, constraint_name)
      WHERE constraint_type = 'FOREIGN KEY'
      AND ccu.table_name = '$ref_table'
      AND ccu.table_schema = '$ref_schema'
      AND ccu.column_name = '$ref_column'
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
  public function setForeignKey(string $connection, string $schema, string $table, string $column, string $ref_schema, string $ref_table, string $ref_column): bool
  {
    $db = $this->getDb($connection);
    $db->query(
       "ALTER TABLE $schema.$table
       ADD CONSTRAINT ".$table."_".$ref_table."_".$column."_fkey
       FOREIGN KEY ($column)
       REFERENCES $ref_schema.$ref_table ($ref_column);"
    );
    return true; // TODO implement error or none?
  }

  /**
   * @inheritDoc
   */
  public function setAutoincrement(string $connection, string $schema, string $table, string $column): bool
  {
    $db = $this->getDb($connection);
    $db->query(
       "ALTER TABLE $schema.$table ALTER COLUMN $column TYPE SERIAL;"
    );
  }

  /**
   * @inheritDoc
   */
  public function setPrimaryKey(string $connection, string $schema, string $table, string $column): bool
  {
    $db = $this->getDb($connection);


    if(!$this->getPrimaryKeyExists($connection,$schema,$table,$column)) {
      $db->query(
        "ALTER TABLE $schema.$table ADD PRIMARY KEY ($column);"
      );
    }

    /*
    $elem = new dbStructureElement();
    $elem->connection = $connection;
    $elem->schema = $schema;
    $elem->table = $table;
    $elem->column = $column;
    $elem->isprimarykey = true;
    // $elem->type = $this->getDefaultPrimaryKeyType();
    $elem->dbdoc_info = $this->getDefaultPrimaryKeyType();

    $this->modifyColumn($elem);
    */
    // CANNOT CHANGE TYPE to SERIAL!


    // } else {
    //   return false;
    // }
    return true;
  }

  /**
   * @inheritDoc
   */
  public function setUniqueKey(string $connection, string $schema, string $table, string $column): bool
  {
    $db = $this->getDb($connection);
    $db->query(
      "ALTER TABLE $schema.$table ADD UNIQUE ($column);"
    );
    return true;
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

  /**
   * @inheritDoc
   */
  public function getConversionTable(): array
  {
    return $this->conversionTable;
  }

  public function convertModelDataTypeToDbType($t) {
		// check for existing overrides/matching types
    $conversionTable = $this->getConversionTable();
		if(array_key_exists($t,$conversionTable)) {
			// use defined type
			return $conversionTable[$t];
		} else {
			$tArr = explode('_', $t);
			if(array_key_exists($tArr[0], $conversionTable)) {
				// we have a defined underlying db field type
				return $conversionTable[$tArr[0]];
			} else {
				// throw some error, as it is not in our type definition library
        return '';
        // throw new \codename\core\exception('DBDOC_MODEL_DATATYPE_NOT_IN_DEFINITION_LIBRARY', \codename\core\exception::$ERRORLEVEL_ERROR, array($t, $tArr[0]));
			}
		}
	}

  public function getDbPrimaryKeyType() :string {
    return $this->getDefaultPrimaryKeyType();
  }

  /**
   * @inheritDoc
   */
  public function getDefaultPrimaryKeyType(): string
  {
    return "SERIAL"; // or BIGSERIAL?
  }
}
