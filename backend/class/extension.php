<?php

namespace codename\core;

use ReflectionClass;

/**
 * extension base class
 */
abstract class extension extends bootstrap
{
    /**
     * Returns parameters used for injecting the extension
     * into an appstack
     *
     * @return array [injection parameters]
     */
    final public function getInjectParameters(): array
    {
        return [
          'vendor' => $this->getExtensionVendor(),
          'app' => $this->getExtensionName(),
          'namespace' => '\\' . (new ReflectionClass($this))->getNamespaceName(),
        ];
    }

    /**
     * [getExtensionVendor description]
     * @return string [description]
     */
    abstract public function getExtensionVendor(): string;

    /**
     * [getExtensionName description]
     * @return string [description]
     */
    abstract public function getExtensionName(): string;
}
