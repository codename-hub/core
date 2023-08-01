<?php

namespace codename\core\helper;

use codename\core\app;
use codename\core\exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use RegexIterator;

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
     * @param $baseClass
     * @param $namespace
     * @param $basedir
     * @return array [type]            [description]
     * @throws ReflectionException
     * @throws exception
     */
    public static function getImplementationsInNamespace($baseClass, $namespace, $basedir): array
    {
        $appstack = app::getAppstack();
        $results = [];

        $relativeNamespace = null;
        // relative namespace
        if (!str_starts_with($namespace, '\\')) {
            $relativeNamespace = $namespace;
        }

        foreach ($appstack as $app) {
            $dir = app::getHomedir($app['vendor'], $app['app']) . $basedir;

            //
            // NOTE: either the app specifies a differing namespace
            // or we fall back to \vendor\app
            //
            $appNamespace = ($app['namespace'] ?? "\\{$app['vendor']}\\{$app['app']}") . '\\';

            //
            // If a relative namespace is provided,
            // use this for searching in appstack
            //
            $lookupNamespace = $relativeNamespace ? $appNamespace . $namespace : $namespace;

            if (!is_dir($dir)) {
                continue;
            }

            $Directory = new RecursiveDirectoryIterator($dir);
            $Iterator = new RecursiveIteratorIterator($Directory);
            $Regex = new RegexIterator($Iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

            foreach ($Regex as $match) {
                $file = $match[0];

                $pathinfo = pathinfo($file);
                // strip basedir from dirname
                $stripped = str_replace($dir, '', $pathinfo['dirname']);

                $class = $lookupNamespace . str_replace('/', '\\', $stripped) . '\\' . $pathinfo['filename'];
                if (class_exists($class)) {
                    $reflectionClass = (new ReflectionClass($class));

                    //
                    // NOTE: ReflectionClass::getName() (or similar methods)
                    // DO NOT return a leading backslash ('\')
                    //
                    if ($reflectionClass->isAbstract() === false && ($reflectionClass->isSubclassOf($baseClass) || ('\\' . $reflectionClass->getName() === $baseClass))) {
                        $name = substr(str_replace('\\', '_', str_replace($lookupNamespace, '', $class)), 1);
                        $results[] = [
                          'name' => $name,
                          'value' => $name,
                        ];
                    }
                }
            }
        }

        return $results;
    }
}
