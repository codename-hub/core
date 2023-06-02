<?php

namespace codename\core\model\schemeless;

use codename\core\app;
use codename\core\config;
use codename\core\exception;
use codename\core\helper\classes;
use codename\core\model\modelInterface;
use ReflectionClass;
use ReflectionException;

/**
 * model for wrapping classes / loadable modules
 */
abstract class abstractModelModuleLoader extends json implements modelInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPrimaryKey(): string
    {
        return 'module_name';
    }

    /**
     * [getModuleClass description]
     * @param string $value [description]
     * @return string        [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function getModuleClass(string $value): string
    {
        return app::getInheritedClass($this->config->get('base_name') . '_' . $value);
    }

    /**
     * [setClassConfig description]
     * @param string $baseName [description]
     * @param string $baseClass [description]
     * @param string $namespace [description]
     * @param string $baseDir [description]
     */
    protected function setClassConfig(string $baseName, string $baseClass, string $namespace, string $baseDir): void
    {
        $this->config = new config([
          'base_name' => $baseName,
          'base_class' => $baseClass,
          'namespace' => $namespace,
          'base_dir' => $baseDir,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function loadConfig(): config
    {
        return new config([]);
    }

    /**
     * {@inheritDoc}
     * @param string $query
     * @param array $params
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    protected function internalQuery(string $query, array $params = []): array
    {
        $classes = classes::getImplementationsInNamespace(
            $this->config->get('base_class'),
            $this->config->get('namespace'),
            $this->config->get('base_dir')
        );

        $translateInstance = app::getTranslate();

        $result = [];

        foreach ($classes as $r) {
            $name = $r['name'];
            $class = app::getInheritedClass($this->config->get('base_name') . '_' . $name);
            $reflectionClass = (new ReflectionClass($class));

            if ($reflectionClass->implementsInterface('\\codename\\core\\model\\schemeless\\moduleLoaderInterface')) {
                $displayName = $translateInstance->translate($class::getTranslationKey());
            } else {
                $displayName = $name;
            }

            $result[$name] = [
              'module_name' => $name,
              'module_displayname' => $displayName,
            ];
        }

        if (count($this->filter) > 0) {
            $result = $this->filterResults($result);
        }

        uasort($result, function ($a, $b) {
            if ($a['module_displayname'] === $b['module_displayname']) {
                return 0;
            }
            return ($a['module_displayname'] < $b['module_displayname']) ? -1 : 1;
        });

        return $result;
    }
}
