<?php
namespace codename\core\install;
use \codename\core\app;

/**
 * Defines an interface to access various db drivers for their structure/schema
 *
 * @author    Kevin Dargel
 */
abstract class dbStructureInterface {

    /**
     * Returns the driver type this interface was made for (e.g pgsql/postgre, mysql or others)
     * @author Kevin Dargel
     * @return string
     * @access public
     */
    public abstract function getDriverCompat() : string;

    /**
     * Returns some attribute definitions, based on the driver type
     * @author Kevin Dargel
     * @return string
     * @access public
     */
    public abstract function getAttributes($connection, $schema, $table) : array;

    /**
     * Returns true, if the corresponding schema exists in the db
     * otherwise: false
     * @param     connection name
     * @param     schema name
     * @return    bool
     * @author    Kevin Dargel
     * @access public
     */
    public abstract function getSchemaExists($connection, $schema) : bool;

    /**
     * Returns true, if the corresponding table exists in the described schema in the db
     *
     * @param     connection name
     * @param     schema name
     * @param     table name
     * @return    bool
     * @author    Kevin Dargel
     * @access public
     */
    public abstract function getTableExists($connection, $schema, $table) : bool;

    /**
     * Returns true, if the corresponding column exists in the described table (and schema) in the db
     *
     * @param     connection name
     * @param     schema name
     * @param     table name
     * @param     column name
     * @return    bool
     * @author    Kevin Dargel
     * @access public
     */
    public abstract function getColumnExists($connection, $schema, $table, $column) : bool;

    /**
     * Returns the structure (attributes/fields) of a table
     * @param     connection name
     * @param     schema name
     * @param     table name
     * @return    array?
     * @author    Kevin Dargel
     */
    public abstract function getTableStructure(string $connection, string $schema, string $table) : array;


    protected abstract function createSchema(string $connection, string $schema) : bool;
    protected abstract function createTable(string $connection, string $schema, string $table, $elem = null) : bool;
    public abstract function createColumn(dbStructureElement $element) : bool;
    public abstract function modifyColumn(dbStructureElement $element) : bool;
    public abstract function replaceColumn(dbStructureElement $element) : bool;

    /**
     * Returns a db-dependent conversion array for
     * converting data types between the model and db
     * @param
     * @return    void
     * @author
     * @copyright
     */
    public abstract function getConversionTable() : array;

    public abstract function getDbPrimaryKeyType() : string;

    public abstract function convertModelDataTypeToDbType($t);

    /**
     * Configures the autoincrementing state of the corresponding column
     * @param
     * @return    void
     * @author
     * @copyright
     */
    public abstract function setAutoincrement(string $connection, string $schema, string $table, string $column) : bool;

    /**
     * Returns the autoincrement state of the corresponding column
     * @param
     * @return    void
     * @author
     * @copyright
     */
    public abstract function getIsAutoincrement(string $connection, string $schema, string $table, string $column) : bool;

    /**
     * Returns a db-dependent datatype for primary keys (with autoincrementing?)
     * @param
     * @return    void
     * @author
     * @copyright
     */
    public abstract function getDefaultPrimaryKeyType() : string;

    /**
     * Returns true, if the corresponding foreign key exists in the described schema, table and for the column in the db
     * @return    bool
     * @author
     * @copyright
     */
    public abstract function getForeignKeyExists(string $connection, string $schema, string $table, string $column, string $ref_schema, string $ref_table, string $ref_column) : bool;

    /**
     * Creates a new foreign key using the given parameters
     * @return    bool
     * @author
     * @copyright
     */
    public abstract function setForeignKey(string $connection, string $schema, string $table, string $column, string $ref_schema, string $ref_table, string $ref_column) : bool;

    /**
     * Returns true, if the corresponding foreign key exists in the described schema, table and for the column in the db
     * @return    bool
     * @author
     * @copyright
     */
    public abstract function getPrimaryKeyExists(string $connection, string $schema, string $table, string $column) : bool;

    /**
     * Creates a new foreign key using the given parameters
     * @return    bool
     * @author
     * @copyright
     */
    public abstract function setPrimaryKey(string $connection, string $schema, string $table, string $column) : bool;

    /**
     * Returns true, if the corresponding foreign key exists in the described schema, table and for the column in the db
     * @return    bool
     * @author
     * @copyright
     */
    public abstract function getUniqueKeyExists(string $connection, string $schema, string $table, string $column) : bool;

    /**
     * Creates a new foreign key using the given parameters
     * @return    bool
     * @author
     * @copyright
     */
    public abstract function setUniqueKey(string $connection, string $schema, string $table, string $column) : bool;



    /**
     * Safely get a db connection (and compare its driver name with the supported driver by this class)
     * throws an exception if an incompatible driver is used
     * @param
     * @return    void
     * @author
     * @copyright
     */
    protected function getDb($connection) : \codename\core\database  {
      $db = app::getDb($connection);
      if($db->driver == $this->getDriverCompat()) {
        return $db;
      } else {
        throw new exception('','');
      }
    }
}
