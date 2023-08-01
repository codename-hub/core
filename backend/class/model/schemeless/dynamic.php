<?php

namespace codename\core\model\schemeless;

use codename\core\app;
use codename\core\config;
use codename\core\errorstack;
use codename\core\exception;
use codename\core\model;
use codename\core\model\modelInterface;
use codename\core\model\schemeless;
use LogicException;
use ReflectionException;

/**
 * dynamic model
 * readonly?
 */
class dynamic extends schemeless implements modelInterface
{
    /**
     * Contains the driver to use for this model and the plugins
     * @var string $type
     */
    public const DB_TYPE = 'dynamic';

    /**
     * I contain the name of the model to use
     * @var string $name
     */
    protected string $name = '';

    /**
     * I contain the prefix of the model to use
     * @var string $prefix
     */
    protected string $prefix = '';

    /**
     * Creates an instance
     * @param array $modeldata [e.g. app => appname]
     * @throws ReflectionException
     * @throws exception
     * @todo refactor the constructor for no method args
     */
    public function __construct(array $modeldata = [])
    {
        parent::__construct($modeldata);
        $this->errorstack = new errorstack('VALIDATION');
        $this->appname = $this->modeldata->get('app') ?? app::getApp();
        return $this;
    }

    /**
     * [setConfig description]
     * @param string $prefix
     * @param string $name [model name for getting the config itself]
     * @param array|null $config
     * @return model                [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function setConfig(string $prefix, string $name, array $config = null): model
    {
        $this->prefix = $prefix;
        $this->name = $name;
        $this->config = $config ?? $this->loadConfig();
        return $this;
    }

    /**
     * loads a new config file (uncached)
     * @return config
     * @throws ReflectionException
     * @throws exception
     */
    protected function loadConfig(): config
    {
        if ($this->modeldata->exists('appstack')) {
            return new \codename\core\config\json('config/model/' . $this->prefix . '_' . $this->name . '.json', true, false, $this->modeldata->get('appstack'));
        } else {
            return new \codename\core\config\json('config/model/' . $this->prefix . '_' . $this->name . '.json', true);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function search(): model
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(mixed $primaryKey = null): model
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function save(array $data): model
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function copy(mixed $primaryKey): model
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function withFlag(int $flagval): model
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function withoutFlag(int $flagval): model
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function withDefaultFlag(int $flagval): model
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    public function withoutDefaultFlag(int $flagval): model
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    protected function internalQuery(string $query, array $params = [])
    {
    }

    /**
     * {@inheritDoc}
     * @return array
     * @throws ReflectionException
     * @throws exception
     */
    protected function internalGetResult(): array
    {
        $this->doQuery('');
        return $this->result;
    }

    /**
     * {@inheritDoc}
     */
    protected function doQuery(string $query, array $params = []): void
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * {@inheritDoc}
     */
    protected function compatibleJoin(model $model): bool
    {
        return false; // ?
    }

    /**
     * [filterResults description]
     * @param array $data [description]
     * @return array       [description]
     */
    protected function filterResults(array $data): array
    {
        throw new LogicException('Not implemented'); // TODO
    }

    /**
     * [mapResults description]
     * @param array $data [description]
     * @return array       [description]
     */
    protected function mapResults(array $data): array
    {
        return $data;
    }
}
