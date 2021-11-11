<?php
namespace codename\core\helper;

use codename\core\app;

/**
 * classes helper
 * pure technical helpers, e.g. for finding implementations in a namespace
 * for cross-application/lib/ext inheritance
 */
class classes
{
  /**
   * returns classes  that
   * - implement a specific base class
   * - are found in a specific namespace (and sub-namespaces)
   * - are found in a specific folder structure
   *
   * SKIP:
   * - abstract classes
   * - classes that do not inherit from the specific base class
   *
   * oh, this is so messy.
   *
   * @param  [type] $baseClass [description]
   * @param  [type] $namespace [description]
   * @param  [type] $basedir   [description]
   * @return [type]            [description]
   */
  public static function getImplementationsInNamespace($baseClass, $namespace, $basedir) {

    $appstack = app::getAppstack();
    $results = [];

    $relativeNamespace = null;
    // relative namespace
    if(substr($namespace, 0, 1) !== '\\') {
      $relativeNamespace = $namespace;
    }

    // DEBUG:
    // \codename\core\app::getResponse()->setData('getImplementationsInNamespace_appstack', $appstack);

    foreach($appstack as $app) {

      $dir = app::getHomedir($app['vendor'], $app['app']).$basedir;

      //
      // NOTE: either the app specifies a differing namespace
      // or we fallback to \vendor\app
      //
      $appNamespace = ($app['namespace'] ?? "\\{$app['vendor']}\\{$app['app']}") . '\\';

      //
      // If a relative namespace is provided,
      // use this for searching in appstack
      //
      $lookupNamespace = $relativeNamespace ? $appNamespace.$namespace : $namespace;

      // DEBUG:
      // \codename\core\app::getResponse()->setData(
      //   'getImplementationsInNamespace_app_'.$app['app'].'_'.$baseClass,
      //   [
      //     "baseClass" => $baseClass,
      //     "namespace" => $namespace,
      //     "appNamespace" => $appNamespace,
      //     "lookupNamespace" => $lookupNamespace,
      //     "relativeNamespace" => $relativeNamespace
      //   ]
      // );

      // DEBUG:
      // \codename\core\app::getResponse()->setData('getImplementationsInNamespace_appNamespace', array_merge(
      //   \codename\core\app::getResponse()->getData('getImplementationsInNamespace_appNamespace') ?? [],
      //   [
      //     $appNamespace
      //   ]
      // ));

      if(!is_dir($dir)) {
        continue;
      }

      $Directory = new \RecursiveDirectoryIterator($dir);
      $Iterator = new \RecursiveIteratorIterator($Directory);
      $Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);


      $all = [];
      foreach($Regex as $match) {
        $file = $match[0];

        $pathinfo = pathinfo($file);
        // strip basedir from dirname
        $stripped = str_replace($dir, '', $pathinfo['dirname']);

        $class = $lookupNamespace . str_replace('/', '\\', $stripped) .'\\'. $pathinfo['filename'];

        // // DEBUG:
        // \codename\core\app::getResponse()->setData('getImplementationsInNamespace', array_merge(
        //   \codename\core\app::getResponse()->getData('getImplementationsInNamespace') ?? [],
        //   [
        //     [
        //       'dir' => $dir,
        //       'file' => $file,
        //       // 'file_pathinfo_dirname' => $pathinfo['dirname'],
        //       'file_pathinfo' => $pathinfo,
        //       'stripped' => $stripped,
        //       'lookup_namespace' => $lookupNamespace,
        //       'class' => $class
        //     ]
        //   ]
        // ));

        $all[] = $class;
        if(class_exists($class)) {
          $reflectionClass = (new \ReflectionClass($class));

          // DEBUG:
          // \codename\core\app::getResponse()->setData('classes_compare', array_merge(
          //   \codename\core\app::getResponse()->getData('classes_compare') ?? [],
          //   [
          //     "$baseClass <=> ".'\\'.$reflectionClass->getNamespaceName()
          //   ]
          // ));

          //
          // NOTE: ReflectionClass::getName() (or similar methods)
          // DO NOT return a leading backslash ('\')
          //
          if($reflectionClass->isAbstract() === false && ($reflectionClass->isSubclassOf($baseClass) || ('\\'.$reflectionClass->getName() === $baseClass))) {
            $name = substr(str_replace('\\', '_', str_replace($lookupNamespace, '', $class)), 1);
            $results[] = [
              'name' => $name,
              'value' => $name
            ];
          }
        }
      }
    }

    // DEBUG:
    // \codename\core\app::getResponse()->setData('getImplementationsInNamespace_modules', $results);

    return $results;
  }
}
