<?php
namespace codename\core\install;
use \codename\core\app;

class dbStructureComparer {

  /**
   * nonsense constructor. whatever
   */
  public function __construct()
  {
  }

  /**
	 * @return \codename\core\install\dbStructureInterface
	 */
	public static function getStructureInterface($connectionString) {
		$dbConfig = app::getEnvironment()->get(app::getEnv(). '>database>' . $connectionString);
		if($dbConfig['driver'] == 'postgresql') {
			return new \codename\core\install\pgsqlStructureInterface();
		}
		if($dbConfig['driver'] == 'mysql') {
			return new \codename\core\install\mysqlStructureInterface();
    }
		return null;
	}

  /**
   * Compares the current db structure with a given definition array
   *
   * @param
   * @return    void
   * @author
   * @copyright
   */
  public function Compare(array $definitions) {

    $elements = array();

		foreach($definitions as $schema => $tables) {
			foreach($tables as $table => $fields) {

				$connection = $fields['connection'] ?? 'default';
        $structureInterface = self::getStructureInterface($connection);
        if($structureInterface == null) {
          continue;
        }

        // Set to null for a later model-based approach, if needed
        $attribs = null;

        // Handling existing schema structure data
				if($structureInterface->getSchemaExists($connection, $schema)) {
				    // Schema exists
            if($structureInterface->getTableExists($connection, $schema, $table)) {
                // Table exists
                $attribs = $structureInterface->getTableStructure($connection,$schema,$table);
            }
        }

        foreach($fields['field'] as $column) {
							$state = 'none';
							$state_info = null;
              $state_data = null;
              $attrib = null;

              if($attribs != null) {
                // check if column exists in DB
                // and set attrib to a non-null value
                foreach($attribs as $dbcolumn) {
                  if($dbcolumn->column == $column) {
                    $attrib = $dbcolumn;
                  }
                }
              }

              // First case:
              // Attribute is in DB. Now compare the structure
              if($attrib != null) {

								if(array_key_exists($attrib->column, $fields['datatype'])) {

									// Get the should-be state of the db field type (based on model)
									$convType = $structureInterface->convertModelDataTypeToDbType($fields['datatype'][$attrib->column]);

									if((is_array($convType) && in_array($attrib->type, $convType)) || ($attrib->type == $convType)) {
										// correct db field type
										$state = dbStructureElement::STATE_OK;
									} else {
										// incorrect db field type. Propose a change to $convType
										$state = dbStructureElement::STATE_CHANGE;
										$state_data = is_array($convType) ? $convType[0] : $convType;
                    $state_info = $state_data;
									}
								} else {
									// Missing datatype in model definition (json)
									$state = dbStructureElement::STATE_INVALID_CONFIG;
                  $state_data = dbStructureElement::STATE_MISSING_DATATYPE;
                  $state_info = $state_data;
								}


              } else {

                $attrib = new dbStructureElement();
                $attrib->driver = $structureInterface->getDriverCompat();
                $attrib->connection = $connection;
                $attrib->schema = $schema;
                $attrib->table = $table;
                $attrib->column = $column;


                // Column seems to be missing in db
                if(array_key_exists($column, $fields['datatype'])) {
                  $state = dbStructureElement::STATE_ADD;
                  $convertedType = $structureInterface->convertModelDataTypeToDbType($fields['datatype'][$column]);
                  $state_data = is_array($convertedType) ? $convertedType[0] : $convertedType;
                  $state_info = $state_data;
                } else {
                  $state = dbStructureElement::STATE_INVALID_CONFIG;
                  $state_data = dbStructureElement::STATE_MISSING_DATATYPE; // DEFAULT?
                  $state_info = $state_data;
                }


                // OVERRIDE:
                // Handling the creation of a to-be-added Column with Primary Key constraint
                // AS A SERIAL/autoincrement
                if(array_key_exists('primary', $fields)) {
                  if(in_array($column, $fields['primary'])) {
                    $attrib->isprimarykey = true;
                    $state = dbStructureElement::STATE_ADD;
                    $state_data = $structureInterface->getDbPrimaryKeyType();
                    $state_info = $state_data;
                  }
                }
              }

              // Reminder: only works, if state is OK until here.
              // This section handles the case of columns to be changed to primaries, afterwards
              if($state == 'OK') {
                if(array_key_exists('primary', $fields)) {
                  if(in_array($column, $fields['primary'])) {
                    $attrib->isprimarykey = true;

                    if($structureInterface->getPrimaryKeyExists($connection, $schema, $table, $column)) {
                      // Check for existance of autoincrement setting
                      if(!$structureInterface->getIsAutoincrement($connection, $schema, $table, $column)) {
                        $state = dbStructureElement::STATE_CHANGE_AUTOINCREMENT; // ?
                        $state_data = null; // $this->getDbPrimaryKeyType();
                        $state_info = $state_data;
                      }
                      // Otherwise: is ok.

                    } else {
                      // We're only continuing, if the column is OK in general.
                      $state = dbStructureElement::STATE_CHANGE_PRIMARYKEY; // ?
                      $state_data = $structureInterface->getDbPrimaryKeyType();
                      $state_info = $state_data;
                      // $state_info = autoincrement settings?
                    }
                  }
                }
              }

              // Reminder: only works, if state is OK until here.
              if($state == 'OK') {
                // May contain array in array?
                if(array_key_exists('unique', $fields)) {
                  if(in_array($column, $fields['unique'])) {
                    $attrib->isunique = true;

                    if(!$structureInterface->getUniqueKeyExists($connection, $schema, $table, $column)) {
                      // We're only continuing, if the column is OK in general.
                      $state = dbStructureElement::STATE_CHANGE_UNIQUEKEY;
                      // $state_info = ?
                    }
                  }
                }
              }


              // Reminder: only works, if state is OK until here.
              if($state == 'OK') {
                if(array_key_exists('foreign', $fields)) {
                  if(array_key_exists($column, $fields['foreign'])) {

                    if(!array_key_exists('schema', $fields['foreign'][$column])
                    || !array_key_exists('table', $fields['foreign'][$column])
                    || !array_key_exists('key', $fields['foreign'][$column])) {

                      $state = dbStructureElement::STATE_INVALID_CONFIG;
                      $state_data = dbStructureElement::STATE_INCOMPLETE_FOREIGN_KEY_CONFIG;
                      $state_info = $state_data;

                    } else {

                      $constraintConfig =  array(
                        'schema' => $fields['foreign'][$column]['schema'],
                        'table' => $fields['foreign'][$column]['table'],
                        'key' => $fields['foreign'][$column]['key'],
                      );

                      if(!$structureInterface->getForeignKeyExists($connection, $schema, $table, $column, $constraintConfig['schema'], $constraintConfig['table'], $constraintConfig['key'])) {
                        // We're only continuing, if the column is OK in general.

                          $state = dbStructureElement::STATE_CHANGE_FOREIGNKEY;
                          $state_info = implode('.', $constraintConfig);
                          $state_data = urlencode(serialize($constraintConfig));
                      }
                    }
                  }
                }
              }

              $attrib->dbdoc_state = $state;
              $attrib->dbdoc_data = $state_data;
              $attrib->dbdoc_info = $state_info;

              $elements[] = $attrib;
              /*
							$schemes[$connection][$schema][$table][] = array(
									'column' => $attrib->column,
									'type' => $attrib->type,
									'notnull' => $attrib['notnull'],
									'hasdefaultvalue' => $attrib['hasdefaultvalue'],
									'DBDOC' => $state,
									'DBDOC_INFO' => $state_info
							);
              */

            }


          }
        }
          return $elements;
  }

  /*
  protected function getDbPrimaryKeyType() :string {
    return $this->dbStructureInterface->getDefaultPrimaryKeyType();
  }


  protected function convertModelDataTypeToDbType($t) : string {
		// check for existing overrides/matching types
    $conversionTable = $this->dbStructureInterface->getConversionTable();
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
				throw new exception('42', '');
			}
		}
	}
  */

}
