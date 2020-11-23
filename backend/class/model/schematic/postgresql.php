<?php
namespace codename\core\model\schematic;
use \codename\core\app;

/**
 * postgreSQL's specific SQL commands
 * @package core
 * @since 2016-02-04
 */
abstract class postgresql extends \codename\core\model\schematic\sql implements \codename\core\model\modelInterface {

    /**
     * @todo DOCUMENTATION
     */
    CONST DB_TYPE = 'postgresql';

    /**
     * custom wrapping override due to PG's case sensitivity
     * @param  string $identifier [description]
     * @return string             [description]
     */
    protected function wrapIdentifier(string $identifier): string {
      // TODO: might be optional by configuring case sensitivity mode?
      return '"'.$identifier.'"';
    }

    /**
     * @inheritDoc
     */
    public function getFilterQuery(
      array &$appliedFilters = array(),
      ?string $mainAlias = null
    ): string {
      $mainAlias = $mainAlias ?? "{$this->schema}.{$this->table}";
      return parent::getFilterQuery($appliedFilters, $mainAlias);
    }

    /**
     * @inheritDoc
     */
    protected function getCurrentFieldlistNonRecursive(
      string $alias = null,
      array &$params
    ): array {
      $result = array();
      if(\count($this->fieldlist) == 0 && \count($this->hiddenFields) > 0) {
        //
        // Include all fields but specific ones
        //
        foreach($this->config->get('field') as $fieldName) {
          if($this->config->get('datatype>'.$fieldName) !== 'virtual') {
            if(!in_array($fieldName, $this->hiddenFields)) {
              if($alias != null) {
                $result[] = array($alias, $this->wrapIdentifier($fieldName));
              } else {
                $result[] = array($this->schema, $this->table, $this->wrapIdentifier($fieldName));
              }
            }
          }
        }
      } else {
        if(count($this->fieldlist) > 0) {
          //
          // Explicit field list
          //
          foreach($this->fieldlist as $field) {
            if($field instanceof \codename\core\model\plugin\calculatedfield\calculatedfieldInterface) {

              //
              // custom field calculation
              //
              $result[] = array($field->get());

            } else if($field instanceof \codename\core\model\plugin\aggregate\aggregateInterface) {

              //
              // pre-defined aggregate function
              //
              $result[] = array($field->get($alias));
            } else if($field instanceof \codename\core\model\plugin\fulltext\fulltextInterface) {

              //
              // pre-defined aggregate function
              //

              $var = $this->getStatementVariable(array_keys($params), $field->getField(), '_ft');
              $params[$var] = $this->getParametrizedValue($field->getValue(), 'text');
              $result[] = array($field->get($var, $alias));

            } else if($this->config->get('datatype>'.$field->field->get()) !== 'virtual' && (!in_array($field->field->get(), $this->hiddenFields) || $field->alias)) {
              //
              // omit virtual fields
              // they're not part of the DB.
              //
              $fieldAlias = $field->alias !== null ? $field->alias->get() : null;
              if($alias != null) {
                if($fieldAlias) {
                  $result[] = [ $alias, $this->wrapIdentifier($field->field->get()) . ' AS ' . $this->wrapIdentifier($fieldAlias) ];
                } else {
                  $result[] = [ $alias, $this->wrapIdentifier($field->field->get()) ];
                }
              } else {
                if($fieldAlias) {
                  $result[] = [ $field->field->getSchema() ?? $this->schema, $field->field->getTable() ?? $this->table, $this->wrapIdentifier($field->field->get()) . ' AS ' . $this->wrapIdentifier($fieldAlias) ];
                } else {
                  $result[] = [ $field->field->getSchema() ?? $this->schema, $field->field->getTable() ?? $this->table, $this->wrapIdentifier($field->field->get()) ];
                }
              }
            }
          }

          //
          // add the rest of the data-model-defined fields
          // as long as they're not hidden.
          //
          foreach($this->config->get('field') as $fieldName) {
            if($this->config->get('datatype>'.$fieldName) !== 'virtual') {
              if(!in_array($fieldName, $this->hiddenFields)) {
                if($alias != null) {
                  $result[] = array($alias, $this->wrapIdentifier($fieldName));
                } else {
                  $result[] = array($this->schema, $this->table, $this->wrapIdentifier($fieldName));
                }
              }
            }
          }

          //
          // NOTE:
          // array_unique can be used on arrays that contain objects or sub-arrays
          // you need to use SORT_REGULAR for this case (!)
          //
          $result = array_unique($result, SORT_REGULAR);

        } else {
          //
          // No explicit fieldlist
          // No explicit hidden fields
          //
          if(count($this->hiddenFields) === 0) {
            //
            // The rest of the fields. Simply using a wildcard
            //
            if($alias != null) {
              $result[] = array($alias, '*');
            } else {
              $result[] = array($this->schema, $this->table, '*');
            }
          } else {
            // ugh?
          }
        }
      }

      return $result;
    }

}
