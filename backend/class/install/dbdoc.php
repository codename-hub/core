<?php
namespace codename\core\install;
use \codename\core\app;
use codename\core\exception;

/**
 * Database Doctor
 * Compares model data with database structure/schema
 * @package core
 * @author Kevin Dargel
 * @since 2016-10-07
 */
class dbdoc {

	/**
	 * Initialize a new dbDoc instance
	 */
	public function __CONSTRUCT() {
	}

	/*
	 *
	 * WARNING
	 *
	 * When changing tables, make sure you're using the CORRECT USER / ---OWNER---
	 *
	 */

	 public function Start() {
		 $mdl = new modelDefinitionLoader();
		 $def = $mdl->getDefinitions();
		 $dbsc = new dbStructureComparer();
		 return $dbsc->Compare($def);
	 }

	public function Fix (dbStructureElement $elem) {

		$dbsi = dbStructureComparer::getStructureInterface($elem->connection);
		// $dbsc = new dbStructureComparer($dbsi);

		if($elem->dbdoc_state == dbStructureElement::STATE_ADD || $elem->dbdoc_state == dbStructureElement::STATE_MISSING_SCHEMA ) {
			$dbsi->createColumn($elem);
			if($elem->isprimarykey) {
				$dbsi->setPrimaryKey($elem->connection,$elem->schema,$elem->table,$elem->column);
				// determine autoincrement state before...
				// $dbsi->setAutoincrement($elem->connection,$elem->schema,$elem->table,$elem->column)
			}
		} else if($elem->dbdoc_state == dbStructureElement::STATE_CHANGE) {
			$dbsi->modifyColumn($elem);
		} else if($elem->dbdoc_state == dbStructureElement::STATE_CHANGE_FOREIGNKEY) {
			$cfg = unserialize(urldecode($elem->dbdoc_data));
			$dbsi->setForeignKey($elem->connection, $elem->schema, $elem->table, $elem->column, $cfg['schema'], $cfg['table'], $cfg['key']);
		} else if($elem->dbdoc_state == dbStructureElement::STATE_CHANGE_PRIMARYKEY) {
			$dbsi->setPrimaryKey($elem->connection, $elem->schema, $elem->table, $elem->column);
		} else if($elem->dbdoc_state == dbStructureElement::STATE_CHANGE_AUTOINCREMENT) {
			$dbsi->setAutoincrement($elem->connection, $elem->schema, $elem->table, $elem->column);
		} else if($elem->dbdoc_state == dbStructureElement::STATE_CHANGE_UNIQUEKEY) {
			// TODO: Handle multiple, multicolumn and multiple multicolumn unique constraints !
			$dbsi->setUniqueKey($elem->connection, $elem->schema, $elem->table, $elem->column);
		}
	}


	public function Start_old() {
		$def = $this->getDefinitions();
		$dbinfo = $this->getDbInfo($def);
		return $dbinfo;
	}


	protected function getDbInfo($definitions) {
		$schemes = array();

		foreach($definitions as $schema => $tables) {
			foreach($tables as $table => $fields) {

				// $connection = isset($fields['connection'])==false ? 'default' : $fields['connection'];
				$connection = $fields['connection'] ?? 'default';

				$structureInterface = dbStructureComparer::getStructureInterface($connection);

				// $structureInterface->
				$ex = $structureInterface->getSchemaExists($connection, $schema);

				if($ex) {
					// Schema exists
					try {
						// TODO: zero-based index error fallback...
						$attribs = $structureInterface->getAttributes($connection,$schema,$table);
						foreach($attribs as $attrib) {

							$state = dbStructureElement::STATE_NONE;
							// comparison?

							$state_info = null;

							if(in_array($attrib['column'], $fields['field'])) {
								// COLUMN EXISTS

								// compare datatype

								if(array_key_exists($attrib['column'], $fields['datatype'])) {

									// Get the should-be state of the db field type (based on model)
									$convType = self::ConvertModelDataTypeToDbType($fields['datatype'][$attrib['column']]);

									if($attrib['type'] == $convType) {
										// correct db field type
										$state = dbStructureElement::STATE_OK;
									} else {
										// incorrect db field type
										$state = dbStructureElement::STATE_CHANGE; // ['.$attrib['type'].'->'.$convType.']';
										$state_info = $attrib['type'].'->'.$convType;
									}
								} else {
									// Missing datatype in model definition
									$state = dbStructureElement::STATE_MISSING_DATATYPE;
								}


							} else {
								// COLUMN DOESNT EXIST
								$state = dbStructureElement::STATE_ADD;
							}

							$schemes[$connection][$schema][$table][] = array(
									'column' => $attrib['column'],
									'type' => $attrib['type'],
									'notnull' => $attrib['notnull'],
									'hasdefaultvalue' => $attrib['hasdefaultvalue'],
									'DBDOC' => $state,
									'DBDOC_INFO' => $state_info
							);

						}
					} catch (\Exception $e) {
						// REMINDER: cannot cast \Exception to a regular Exception
						$schemes['errors'][] = array($schema => $e);
					}
				} else {
					// Schema doesn't exist
					// $schemes['missing'][] = $schema;
					$state = dbStructureElement::STATE_MISSING_SCHEMA;
					$state_info = "";

					foreach($fields['field'] as $modelField) {

						$dbType = 'integer'; // default
						if(array_key_exists($modelField, $fields['datatype'])) {
							$dbType = self::ConvertModelDataTypeToDbType($fields['datatype'][$modelField]);
						}
						$state_info = '';

						$schemes[$connection][$schema][$table][] = array(
								'column' => $modelField,
								'type' => $dbType,
								// 'notnull' => $attrib['notnull'],
								// 'hasdefaultvalue' => $attrib['hasdefaultvalue'],
								'DBDOC' => $state,
								'DBDOC_INFO' => $state_info
						);
					}
				}
			}
		}

		return $schemes;
	}

	/**
	 * Contains local datatypes => underlying db (field) types (CURRENTLY ONLY FOR PGSQL!)
	 * @var array
	 */
	protected static $translateModelDataTypeToDbType = array(
			'text' => 'text',
			'text_timestamp' => 'timestamp without time zone',
			'text_date' => 'date',
			'number' => 'number',
			'number_natural' => 'integer',
			'boolean' => 'boolean',
			'structure' => 'text',
	);

	// protected static $translateDbTypeToModelDataType = null;


	protected static function ConvertModelDataTypeToDbType($t) : string {
		// check for existing overrides/matching types
		if(array_key_exists($t,self::$translateModelDataTypeToDbType)) {
			// use defined type
			return self::$translateModelDataTypeToDbType[$t];
		} else {
			$tArr = explode('_', $t);
			if(array_key_exists($tArr[0], self::$translateModelDataTypeToDbType)) {
				// we have a defined underlying db field type
				return self::$translateModelDataTypeToDbType[$tArr[0]];
			} else {
				// throw some error, as it is not in our type definition library
				throw new exception('42', '');
			}
		}
	}

	/*
	attnum, -- column number. not so important?
	attname AS column, -- column name
	-- atttypid,
	-- atttypmod,
	format_type(atttypid, atttypmod) AS type, -- formatted type
	-- attstattarget as stattarget,
	-- attlen as len, -- needed??
	attnotnull as notnull,
	-- attcacheoff as cacheoff,
	-- attbyval as byval,
	-- attstorage as stor,
	-- attalign as align,
	atthasdef as hasdefaultvalue -- hasdefaultvalue
	*/
	/*
	protected $postgresqlatt = array(
			// 'attnum' => 'colnum',
			'attname' => 'column',
			'format_type(atttypid, atttypmod)' => 'type',
			'attnotnull' => 'notnull',
			'atthasdef' => 'hasdefaultvalue'
	);
	*/

	/**
	 * Gets the all models/definitions, also inherited
	 * @return \codename\core\multitype
	 */
	protected function getDefinitions() {

		$models = array();

		foreach(app::getAppstack() as $app ) {
			// array of vendor,app
			$appdir = app::getHomedir($app['vendor'], $app['app']);
			$dir = $appdir . "config/model";

			// get all model json files, first:
			$files = app::getFilesystem()->dirList( $dir );

			foreach($files as $f) {

				$file = $dir . '/' . $f;
				// check for .json extension
				$fileInfo = new \SplFileInfo($file);
				if($fileInfo->getExtension() === 'json') {
					$modelname = $fileInfo->getBasename('.json'); // filename w/o extension
					// $models[] = $modelname;

					$comp = explode( '_' , $modelname);
					$schema = $comp[0];
					$table = $comp[1];

					// \$app['vendor']\$app['app']\$modelname();
					$got = (new \codename\core\config\json("config/model/" . $fileInfo->getFilename(), true, true))->get();
					$models[$schema][$table] = $got;
				}
			}
		}
		return $models;
	}

	public function Test() : string {
		$files = app::getFilesystem()->dirList( app::getHomedir() . "config/model");
		return var_export($files,true);
	}

}
