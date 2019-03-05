<?php
namespace codename\core\model\plugin\filter;

/**
 * Tell a model to filter the results
 * @package core
 * @since 2016-02-04
 */
class json extends \codename\core\model\plugin\filter
  implements \codename\core\model\plugin\filter\filterInterface, \codename\core\model\plugin\filter\executableFilterInterface {

  /**
   * @inheritDoc
   */
  public function matches(array $data): bool
  {
    $fieldName = $this->field->get();
    if(($this->operator == '=') || ($this->operator == '!=')) {
      // check for (un)equality
      if(is_array($this->value)) {
        //
        // SQL Simile: IN (...) / NOT IN (...)
        //
        if(!array_key_exists($fieldName, $data) || !in_array($data[$fieldName], $this->value)) {
          return ($this->operator == '!=');
        } else {
          return ($this->operator == '=');
        }
      } else {
        //
        // SQL Simile: = / != / <>
        //
        if(!array_key_exists($fieldName, $data) || $data[$fieldName] !== $this->value) {
          return ($this->operator == '!=');
        } else {
          return ($this->operator == '=');
        }
      }
    } else if(($this->operator == '>=')
      || ($this->operator == '<=')
      || ($this->operator == '>')
      || ($this->operator == '<')) {

      //
      if(array_key_exists($fieldName, $data)) {

        $dataValue = $data[$fieldName];
        if(is_numeric($this->value)) {
          // integer comparison
          if(is_int($this->value)) {
            return ($this->operator == '>=' && $dataValue >= $this->value) ||
              ($this->operator == '<=' && $dataValue <= $this->value) ||
              ($this->operator == '>' && $dataValue > $this->value) ||
              ($this->operator == '<' && $dataValue < $this->value);
          } else if(is_float($this->value)) {
            // float/double comparison
            return ($this->operator == '>=' && bccomp($dataValue, $this->value) >= 0) ||
              ($this->operator == '<=' && bccomp($dataValue, $this->value) <= 0) ||
              ($this->operator == '>' && bccomp($dataValue, $this->value) === 1) ||
              ($this->operator == '<' && bccomp($dataValue, $this->value) === -1);
          } else {
            var_dump($this->value);
          }
        } else {
          die("non-numeric");
        }

        die("error");
      }
    } else if(($this->operator == 'LIKE')
      || ($this->operator == 'ILIKE')
      ) {
        $dataValue = $data[$fieldName];

        // case-(in)-sensitive string matching
        if(strlen($dataValue) === 0 || strlen($this->value) === 0) {
          return strlen($dataValue) == strlen($this->value); // pretty stupid. if we like to have it 'equal' we can simply rely on this.
        } else {

          $operator = $this->operator;
          $strposFunc = function(string $haystack, string $needle, int $offset = 0) use ($operator) {
            return $operator == 'ILIKE' ? stripos($haystack, $needle, $offset) : strpos($haystack, $needle, $offset);
          };

          // catch case: single or double wildcard only, e.g '%' or '%%'

          // wildcard at beginning
          $wildcardStart = $this->value[0] == '%';
          $wildcardEnd = $this->value[strlen($this->value)-1] == '%';

          $needle = substr($this->value, ($wildcardStart ? 1 : 0), strlen($this->value) - ($wildcardStart ? 1 : 0) - ($wildcardEnd ? 1 : 0));
          $needlePos = $strposFunc($dataValue, $needle);

          // echo("<br>Needle: '{$needle}', Value: '{$dataValue}' ");

          // no match at all
          if($needlePos === false) {
            // echo(" -- not found");
            return false;
          }

          if(!$wildcardStart && $needlePos > 0) {
            // no wildcard, string MUST start with needle
            // echo(" -- !wildcardStart && needlePos > 0");
            return false;
          }
          // we may fix:
          if(!$wildcardEnd && $needlePos != (strlen($this->value)-strlen($needle))) {
            // echo(" -- !wildcardEnd && needlePos != " . (strlen($this->value)-strlen($needle)));
            return false;
          }

          // otherwise, everything's ok!
          return true;

        }
    } else {
      return false;
    }
  }

  /**
   * @inheritDoc
   */
  public function getFieldValue(string $tableAlias = null): string
  {
    //
    // no table alias
    //
    return $this->field->getValue();
  }

}
