<?php

namespace codename\core\model\schemeless;

use codename\core\app;
use codename\core\config;
use codename\core\errorstack;
use codename\core\exception;
use codename\core\model;
use codename\core\model\modelInterface;
use codename\core\model\plugin\filter\executableFilterInterface;
use codename\core\model\schemeless;
use LogicException;
use ReflectionException;

/**
 * model for a json data source (json array)
 * readonly?
 */
abstract class json extends schemeless implements modelInterface
{
    /**
     * Contains the driver to use for this model and the plugins
     * @var string $type
     */
    public const DB_TYPE = 'json';
    /**
     * [protected description]
     * @var array
     */
    protected static array $t_data = [];
    /**
     * Contains the schema this model is based upon
     * @var null|string
     */
    public ?string $schema = null;
    /**
     * Contains the table this model is based upon
     * @var null|string
     */
    public ?string $table = null;
    /**
     * I contain the prefix of the model to use
     * @var string $prefix
     */
    public string $prefix = '';
    /**
     * I contain the path to the XML file that is used
     * @var null|string $file
     */
    protected ?string $file = null;
    /**
     * I contain the name of the model to use
     * @var string $name
     */
    protected $name = '';

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
     * @param null|string $file [data source file, .json]
     * @param string $prefix
     * @param string $name [model name for getting the config itself]
     * @return model                [description]
     * @throws ReflectionException
     * @throws exception
     */
    public function setConfig(?string $file, string $prefix, string $name): model
    {
        $this->file = $file;
        $this->prefix = $prefix;
        $this->name = $name;
        $this->config = $this->loadConfig();
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
            return new config\json('config/model/' . $this->prefix . '_' . $this->name . '.json', true, false, $this->modeldata->get('appstack'));
        } else {
            return new config\json('config/model/' . $this->prefix . '_' . $this->name . '.json', true);
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
     * @return model
     * @throws ReflectionException
     * @throws exception
     */
    public function search(): model
    {
        $this->doQuery('');
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
    protected function internalGetResult(): array
    {
        return $this->result;
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
        $identifier = $this->file . '_' . ($this->modeldata->exists('appstack') ? '1' : '0');
        if (!isset(static::$t_data[$identifier])) {
            if ($this->modeldata->exists('appstack')) {
                $inherit = $this->modeldata->get('inherit') ?? false;
                // traverse (custom) appstack, if we defined it
                static::$t_data[$identifier] = (new config\json($this->file, true, $inherit, $this->modeldata->get('appstack')))->get();
            } else {
                static::$t_data[$identifier] = (new config\json($this->file))->get();
            }

            // map PKEY (index) to a real field
            $pkey = $this->getPrimaryKey();
            array_walk(static::$t_data[$identifier], function (&$item, $key) use ($pkey) {
                if (!isset($item[$pkey])) {
                    $item[$pkey] = $key;
                }
            });
        }

        $data = static::$t_data[$identifier];

        if (count($this->virtualFields) > 0) {
            foreach ($data as &$d) {
                foreach ($this->virtualFields as $field => $function) {
                    $d[$field] = $function($d);
                }
            }
        }

        if ((count($this->filter) > 0) || (count($this->filterCollections) > 0)) {
            $data = $this->filterResults($data);
        }

        return $this->mapResults($data);
    }

    /**
     * [filterResults description]
     * @param array $data [description]
     * @return array       [description]
     * @throws exception
     */
    protected function filterResults(array $data): array
    {
        //
        // special hack
        // to highly speed up filtering for json/array key filtering
        //
        if (count($this->filter) === 1) {
            foreach ($this->filter as $filter) {
                if ($filter->field->get() == $this->getPrimaryKey() && $filter->operator == '=') {
                    if (is_array($filter->value)) {
                        $data = array_values(array_intersect_key($data, array_flip($filter->value)));
                    } else {
                        $data = isset($data[$filter->value]) ? [$data[$filter->value]] : [];
                    }
                }
            }
        }

        $filteredData = $data;

        if (count($this->filter) >= 1) {
            $filteredData = array_filter($filteredData, function ($entry) {
                $pass = null;
                foreach ($this->filter as $filter) {
                    if ($pass === false && $filter->conjunction === 'AND') {
                        continue;
                    }

                    if ($filter instanceof executableFilterInterface) {
                        if ($pass === null) {
                            $pass = $filter->matches($entry);
                        } elseif ($filter->conjunction === 'OR') {
                            $pass = $pass || $filter->matches($entry);
                        } else {
                            $pass = $pass && $filter->matches($entry);
                        }
                    } else {
                        // we may warn for incompatible filters?
                    }
                }

                //
                // NOTE/TODO: What to do, when pass === null ?
                //
                return $pass;
            });
        }

        if (count($this->filterCollections) > 0) {
            $filteredData = array_filter($filteredData, function ($entry) {
                $collectionsPass = null;

                foreach ($this->filterCollections as $filtercollections) {
                    $groupPass = null;

                    foreach ($filtercollections as $filtercollection) {
                        $groupOperator = $filtercollection['operator'];
                        $conjunction = $filtercollection['conjunction'];
                        $pass = null;

                        foreach ($filtercollection['filters'] as $filter) {
                            // NOTE: singular filter conjunctions in collections default to NULL
                            // as we have an overriding group operator
                            // so, only use the explicit filter conjunction, if given
                            $filterConjunction = $filter->conjunction ?? $groupOperator;

                            // TODO: Group Operator?
                            if ($pass === false && $filterConjunction === 'AND') {
                                continue;
                            }

                            if ($filter instanceof executableFilterInterface) {
                                if ($pass === null) {
                                    $pass = $filter->matches($entry);
                                } elseif ($filterConjunction === 'OR') {
                                    $pass = $pass || $filter->matches($entry);
                                } else {
                                    $pass = $pass && $filter->matches($entry);
                                }
                            } else {
                                // we may warn for incompatible filters?
                            }
                        }

                        if ($groupPass === null) {
                            $groupPass = $pass;
                        } elseif ($conjunction === 'OR') {
                            $groupPass = $groupPass || $pass;
                        } else {
                            $groupPass = $groupPass && $pass;
                        }
                    }

                    if ($collectionsPass === null) {
                        $collectionsPass = $groupPass;
                    } else {
                        $collectionsPass = $collectionsPass && $groupPass;
                    }
                }

                return $collectionsPass;
            });
        }

        return array_values($filteredData);
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

    /**
     * {@inheritDoc}
     */
    protected function compatibleJoin(model $model): bool
    {
        return false;
    }
}
