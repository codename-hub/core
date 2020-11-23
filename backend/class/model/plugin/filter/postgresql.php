<?php
namespace codename\core\model\plugin\filter;

/**
 * Tell a model to filter the results
 * @package core
 * @since 2016-02-04
 */
class postgresql extends \codename\core\model\plugin\filter implements \codename\core\model\plugin\filter\filterInterface {
    /**
     * @inheritDoc
     */
    public function getFieldValue(string $tableAlias = null): string
    {
      // Case sensivity wrappers for PGSQL
      return $tableAlias ? ($tableAlias . '.' . '"'.$this->field->get().'"') : '"'.$this->field->getValue().'"';
    }
}
