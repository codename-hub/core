<?php

namespace codename\core\model\schemeless;

use codename\core\app;
use codename\core\errorstack;
use codename\core\model;
use codename\core\model\modelInterface;
use codename\core\model\schemeless;
use codename\core\value\text\modelfield;
use codename\core\xml2array;
use Exception;
use ReflectionException;

abstract class xml extends schemeless implements modelInterface
{
    /**
     * Contains the driver to use for this model and the plugins
     * @var string $type
     */
    public const DB_TYPE = 'xml';

    /**
     * I contain the path to the XML file that is used
     * @var string $file
     */
    protected string $file = '';

    /**
     * I contain the name of the model to use
     * @var string $name
     */
    protected string $name = '';

    /**
     * Creates an instance
     * @param array $modeldata
     * @todo refactor the constructor for no method args
     */
    public function __construct(array $modeldata)
    {
        parent::__construct($modeldata);
        $this->errorstack = new errorstack('VALIDATION');
        $this->appname = $modeldata['app'];
        return $this;
    }

    /**
     * @todo DOCUMENTATION
     */
    public function search(): model
    {
        return $this;
    }

    /**
     * @todo DOCUMENTATION
     */
    public function save(array $data): model
    {
        return $this;
    }

    /**
     * @todo DOCUMENTATION
     */
    public function copy(mixed $primaryKey): model
    {
        return $this;
    }

    /**
     * @todo DOCUMENTATION
     */
    public function delete(mixed $primaryKey = null): model
    {
        return $this;
    }

    /**
     * @todo DOCUMENTATION
     */
    public function delimit(modelfield $field, $value = null): string
    {
        return $value;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getResult(): array
    {
        $this->doQuery('null');
        return $this->result;
    }

    /**
     * @param string $query
     * @param array $params
     * @throws Exception
     */
    protected function doQuery(string $query, array $params = []): void
    {
        $data = xml2array::createArray(file_get_contents($this->file))['modelEntries']['entry'];

        if (count($this->filter) > 0) {
            $data = $this->filterResults($data);
        }

        $this->result = $this->mapResults($data);
    }

    /**
     * @todo DOCUMENTATION
     */
    protected function filterResults(array $data): array
    {
        $filteredData = [];
        foreach ($data as $entry) {
            $pass = true;
            foreach ($this->filter as $filter) {
                if (!$pass) {
                    continue;
                }
                if (!array_key_exists($filter->field, $entry) || $entry[$filter->field] !== $filter->value) {
                    $pass = false;
                }
            }
            if (!$pass) {
                continue;
            }
            $filteredData[] = $entry;
        }
        return $filteredData;
    }

    /**
     * @param array $data
     * @return array
     * @throws \codename\core\exception
     */
    protected function mapResults(array $data): array
    {
        $results = [];
        foreach ($data as $result) {
            $result[$this->getPrimaryKey()] = $result['@attributes']['id'];
            unset($result['@attributes']);
            $results[] = $result;
        }
        return $results;
    }

    /**
     * @todo DOCUMENTATION
     */
    public function withFlag(int $flagval): model
    {
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     * @throws ReflectionException
     * @throws \codename\core\exception
     */
    public function setConfig(string $name): static
    {
        $this->file = app::getInheritedPath('data/xml/' . $name . '.xml');
        $this->config = new \codename\core\config\json('config/model/' . $name . '.json');
        return $this;
    }
}
