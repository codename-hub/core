<?php
namespace codename\core\model\schemeless;

abstract class xml extends \codename\core\model\schemeless implements \codename\core\model\modelInterface {


    /**
     * Contains the driver to use for this model and the plugins
     * @var string $type
     */
    CONST DB_TYPE = 'xml';
    
    /**
     * I contain the path to the XML file that is used
     * @var string $file
     */
    protected $file = '';
    
    /**
     * I contain the name of the model to use
     * @var string $name
     */
    protected $name = '';

    /**
     * Creates an instance
     * @param array $modeldata
     * @return model
     * @todo refactor the constructor for no method args
     */
    public function __CONSTRUCT(array $modeldata) {
        $this->errorstack = new \codename\core\errorstack('VALIDATION');
        $this->appname = $modeldata['app'];
        return $this;
    }
    
    /**
     * @todo DOCUMENTATION
     */
    public function search() : \codename\core\model {
        return $this;
    }
    
    /**
     * @todo DOCUMENTATION
     */
    public function save(array $data) : \codename\core\model {
        return $this;
    }
    
    /**
     * @todo DOCUMENTATION
     */
    public function copy($primaryKey) : \codename\core\model {
        return $this;
    }
    
    /**
     * @todo DOCUMENTATION
     */
    public function delete($primaryKey = null) : \codename\core\model {
        return $this;
    }
    
    /**
     * @todo DOCUMENTATION
     */
    public function delimit(string $field, $value = NULL): string {
        return $value;
    }
    
    /**
     * @todo DOCUMENTATION
     */
    protected function filterResults(array $data) : array {
        $filteredData = array();
        foreach($data as $entry) {
            $pass = true;
            foreach($this->filter as $filter) {
                if(!$pass) {
                    continue;
                }
                if(!array_key_exists($filter->field, $entry) || $entry[$filter->field] !== $filter->value) {
                    $pass = false;
                    continue;
                }
            }
            if(!$pass) {
                continue;
            }
            $filteredData[] = $entry;
        }
        return $filteredData;
    }
    
    /**
     * @todo DOCUMENTATION
     */
    protected function mapResults(array $data) : array {
        $results = array();
        foreach ($data as $result) {
            $result[$this->getPrimarykey()] = $result['@attributes']['id'];
            unset($result['@attributes']);
            $results[] = $result;
        }
        return $results;
    }

    /**
     * @todo DOCUMENTATION
     */
    protected function doQuery(string $query) : array {
        $data = \codename\core\XML2Array::createArray(file_get_contents($this->file))['modelEntries']['entry'];
        
        if(count($this->filter) > 0) {
            $data = $this->filterResults($data);
        }
        
        return $this->mapResults($data);
    }
    
    /**
     * @todo DOCUMENTATION
     */
    public function getResult() : array {
        return $this->doQuery('null');
    }
    
    /**
     * @todo DOCUMENTATION
     */
    public function withFlag(int $flagval) : \codename\core\model {
        return $this;
    }
    
    /**
     * @todo DOCUMENTATION
     */
    public function setConfig(string $name) {
        $this->file = \codename\core\app::getInheritedPath('data/xml/' . $name . '.xml');
        $this->config = new \codename\core\config\json('config/model/' . $name . '.json');
        return $this;
    }
    
    
}
