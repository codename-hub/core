<?php
namespace codename\core\model\schemeless;
use \codename\core\model;

/**
 * Handling noSQL data from elasticsearch
 * @package core
 * @since 2016-02-04
 */
class elasticsearch extends \codename\core\model\schemeless implements \codename\core\model\modelInterface {

    /**
     * @todo DOCUMENTATION
     */
    CONST DB_TYPE = 'elasticsearch';

    /**
     * @todo DOCUMENTATION
     */
    protected $index = null;
    /**
     * @todo DOCUMENTATION
     */
    protected $type = null;

    /**
     * Contains the elasticsearch client
     * @var \Client
     */
    protected $client = null;

    /**
     * Creates and configures the instance of the model
     * @param string $connection Name of the connection in the app configuration file
     * @param string $schema Schema to use the model for
     * @param string $table Table to use the model on
     * @return model_schematic_postgresql
     */
    public function setConfig(string $connection, string $index, string $type) : model_schemeless_elasticsearch {
        $this->index = $index;
        $this->type = $type;

        require "/server/webserver/domain/honeycomb/vendor/autoload.php";
        $hosts = array(
                '192.168.178.22:9200'
        );
        $this->client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();

        $file = app::getHomedir($this->appname) . 'config/model/' . $this->index . '_' . $this->type . '.json';
        $this->loadConfigfile($file);

        return $this;
    }

    /**
     * @todo DOCUMENTATION
     */
    public function getResult() : array {
        return $this->result['hits']['hits'];
    }


    /**
     * Performs a search with the given criterua from the other functions
     * @return model
     */
    public function search() : model {
        $params = [
            'index' => $this->index,
            'type' => $this->type
        ];
        $this->result = $this->client->search($params);
        return $this;
    }

    /**
     * Deletes the given key from the model
     * @param multitype $primaryKey
     * @return model
     */
    public function delete($primaryKey = null) : model {
        return $this;
    }

    /**
     * saves the given array to the model
     * @param array $primaryKey
     * @return model
     */
    public function save(array $data) : model {
        return $this;
    }

    /**
     * Copies an entry from the component to another one
     * @param multitype $primaryKey
     * @return model
     */
    public function copy($primaryKey) : model {
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function loadConfig() : \codename\core\config
    {
      throw new \LogicException('Not implemented'); // TODO
    }

    /**
     * @inheritDoc
     */
    protected function internalQuery(string $query, array $params = array())
    {
      throw new \LogicException('Not implemented'); // TODO
    }

    /**
     * @inheritDoc
     */
    protected function internalGetResult() : array
    {
      throw new \LogicException('Not implemented'); // TODO
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier() : string
    {
      throw new \LogicException('Not implemented'); // TODO
    }

    /**
     * @inheritDoc
     */
    public function withFlag(int $flagval) : model
    {
      throw new \LogicException('Not implemented'); // TODO
    }

    /**
     * @inheritDoc
     */
    public function withoutFlag(int $flagval) : model
    {
      throw new \LogicException('Not implemented'); // TODO
    }

    /**
     * @inheritDoc
     */
    public function withDefaultFlag(int $flagval) : model
    {
      throw new \LogicException('Not implemented'); // TODO
    }

    /**
     * @inheritDoc
     */
    public function withoutDefaultFlag(int $flagval) : model
    {
      throw new \LogicException('Not implemented'); // TODO
    }

}
