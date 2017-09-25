<?php
namespace codename\core\install;
use codename\core\app;

/**
 * Loads definitions of models (json configs)
 *
 * @param
 * @return    void
 * @author
 * @copyright
 */
class modelDefinitionLoader {
/**
 *
 */
public function __construct()
{

}

public function getDefinitions() : array {
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
}
