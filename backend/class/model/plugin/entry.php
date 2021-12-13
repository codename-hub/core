<?php
namespace codename\core\model\plugin;

/**
 * Handling data of a single entry in a model
 * @package core
 * @since 2016-06-08
 */
class entry {

    /**
     * You are trying to set a field of the current modelEntry instance.
     * <br />The field passed to the method is not a component of this model. So it cannot be set.
     * @var string
     */
    CONST EXCEPTION_FIELDSET_FIELDNOTFOUNDINMODEL = 'EXCEPTION_FIELDSET_FIELDNOTFOUNDINMODEL';

    /**
     * You are trying to get the contents of a field inside the current modelEntry instance.
     * <br />The field passed to the method is not a component of this model. So it cannot be returned.
     * @var string
     */
    CONST EXCEPTION_FIELDGET_FIELDNOTFOUNDINMODEL = 'EXCEPTION_FIELDGET_FIELDNOTFOUNDINMODEL';

    /**
     * Contaions the datacontainer object
     * @var \codename\core\datacontainer
     */
    protected $data = null;

    /**
     * Returns true if the given $field exists in the current model
     * @param string $field
     * @return bool
     */
    public function fieldExists(string $field) : bool {
        return in_array($field, $this->config->get('field'));
    }

    /**
     * Sets the given $field's to $value
     * @param string $field
     * @param mixed|null $value
     * @return \codename\core\model
     */
    public function fieldSet(string $field, $value) : \codename\core\model {
        if(!$this->fieldExists($field)) {
            throw new \codename\core\exception(self::EXCEPTION_FIELDSET_FIELDNOTFOUNDINMODEL, \codename\core\exception::$ERRORLEVEL_FATAL, $field);
        }
        $this->data->setData($field, $value);
        return $this;
    }

    /**
     * Gets the value from the given $field
     * @param string $field
     * @throws \codename\core\exception
     * @return mixed|null
     */
    public function fieldGet(string $field) {
        if(!$this->fieldExists($field)) {
            throw new \codename\core\exception(self::EXCEPTION_FIELDGET_FIELDNOTFOUNDINMODEL, \codename\core\exception::$ERRORLEVEL_FATAL, $field);
        }
        return $this->data->getData($field);
    }

    /**
     * @todo DOCUMENTATION
     */
    public function entryMake(array $data = array()) : \codename\core\model {
        $this->data = new \codename\core\datacontainer($data);
        return $this;
    }

    /**
     * @todo DOCUMENTATION
     */
    public function entryValidate() : array {
        return $this->validate($this->data->getData());
    }

    /**
     * @todo DOCUMENTATION
     */
    public function entryUpdate(array $data) : \codename\core\model {
        foreach($this->getFields() as $field) {

        }
    }

    /**
     * @todo DOCUMENTATION
     */
    public function entryDelete() : \codename\core\model {
        if(is_null($this->data)) {
            return $this;
        }
        $this->delete($this->data->getData($this->getPrimarykey()));
        return $this;
    }

    /**
     * @todo DOCUMENTATION
     */
    public function entryLoad(string $primaryKey) : \codename\core\model {
        $entry = $this->loadByUnique($this->getPrimarykey(), $primaryKey);
        if(count($entry) == 0) {
            return $this;
        }
        $this->entryMake($entry);
        return $this;
    }

    /**
     * @todo DOCUMENTATION
     */
    public function entrySave() : \codename\core\model {
        if(is_null($this->data)) {
            return $this;
        }

        $this->save($this->data->getData());
        return $this;
    }

}
