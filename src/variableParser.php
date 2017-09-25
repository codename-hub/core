<?php
namespace codename\core;
use codename\core\workflow\component\componentBase;

class variableParser {

    /**
     * @var \codename\core\config
     */
    protected $currentContext;

    /**
     *
     */
    public function __construct(\codename\core\config $varContext)
    {
      $this->currentContext = $varContext;
    }

    //@TODO: implement strict mode for exception throwing when error occurs
    public function parse($parseString, $strict = false) : string {

      if(substr_count('{', $parseString) !== substr_count('}', $parseString)) {
        return 'ERROR_INVALID_PARENTHESE_COUNT';
      }

      $pp = new parentheseParser('{','}');
      $replaceTerms = $pp->run($parseString);

      // #DEBUG var_dump($replaceTerms);

      $resultStrings = array();

      foreach($replaceTerms as &$term) {

        $replacement = $this->parseTerm($term);

        // #DEBUG echo('<br>' . '[INTERNAL] Replace '.$term. ' with ' . $replacement);
        $resultStrings[$term] = $replacement; //str_replace($term, $replacement, $parseString);

        foreach($replaceTerms as &$v) {
          if($v !== $term) {
            // #DEBUG echo('<br> Replacing ' . $term .  ' with ' . $replacement . ' -- Before: '.$v);
            $v = str_replace($term, $replacement, $v);
            // #DEBUG echo(' -- After: '.$v);
          }
        }

        foreach($resultStrings as $k => &$v) {
          // #DEBUG echo('<br> Replacing ' . $term .  ' with ' . $replacement . ' -- Before: '.$v);
          $v = str_replace($term, $replacement, $v);
          // #DEBUG echo(' -- After: '.$v);
        }


      }

      // #DEBUG var_dump($resultStrings);

      foreach($resultStrings as $k => &$v) {
        // echo('<br>' . '[FINAL] Replace '.$k. ' with ' . $v);
        $parseString = str_replace($k, $v, $parseString);
        // echo(' => '.$parseString);
      }

      return $parseString;
    }


    const CURRENT_CONTEXT_IDENTIFIER = 'current';


    protected static function replaceLastOccurrence($subject, $search, $replaceWith) {
      // $subject is the original string
      // $search is the thing you want to replace
      // $replace is what you want to replace it with
      if(strrpos($subject, $search) !== false) {
        return substr_replace($subject, $replaceWith, strrpos($subject, $search), strlen($search));
      } else {
        return 'ERROR_REPLACE_LAST_OCCURRENCE';
      }
    }

    protected static function replaceFirstOccurrence($subject, $search, $replaceWith) {
      // $subject is the original string
      // $search is the thing you want to replace
      // $replace is what you want to replace it with
      if(strrpos($subject, $search) !== false) {
        return substr_replace($subject, $replaceWith, strpos($subject, $search), strlen($search));
      } else {
        return 'ERROR_REPLACE_LAST_OCCURRENCE';
      }
    }

    protected function parseTerm($parseVariableString) : string {

      // get the inner term
      $term = self::replaceFirstOccurrence($parseVariableString, '{', '');
      $term = self::replaceLastOccurrence($term, '}', '');

      // get variable components
      $variableComponents = $this->parseVariableComponents(
        $term
      );

      if(sizeof($variableComponents) == 2) {

        // namespace:variablename
        if($variableComponents[0] == self::CURRENT_CONTEXT_IDENTIFIER) {
          // look in current context (this config)
          if(strpos($variableComponents[1], '(') !== false && substr_count('(', $variableComponents[1]) === substr_count(')', $variableComponents[1])) {
            $functionComponents = $this->parseFunctionComponents($variableComponents[1]);
            if($this->currentContext->exists($functionComponents[0]) == true) {
              if(is_callable($this->currentContext->get($functionComponents[0]))) {

                // #DEBUG var_dump($variableComponents[0]);
                // #DEBUG var_dump($functionComponents);

                return call_user_func_array(
                  $this->currentContext->get($functionComponents[0]),
                  $functionComponents[1]
                );
              } else {
                return 'NOT_A_FUNCTION';
              }
            } else {
              return 'FUNCTION_DOES_NOT_EXIST';
            }
          } else {
            if($this->currentContext->exists($variableComponents[1]) == true) {
              return $this->currentContext->get($variableComponents[1]);
            } else {
              return 'UNDEFINED_VARIABLE';
            }
          }
        } else {
          // variable not in current context, look elsewhere...
          // namespace is app
          /* foreach(app::getAppstack() as $app) {
            $app->
          }*/
          return 'NOT_IMPLEMENTED_VARIABLE_NAMESPACE';
        }
      } elseif(sizeof($variableComponents) == 1) {
        // check for the term being a function
        if(strpos($variableComponents[0], '(') !== false && substr_count('(', $variableComponents[0]) === substr_count(')', $variableComponents[0])) {
          $functionComponents = $this->parseFunctionComponents($variableComponents[0]);
          if($this->currentContext->exists($functionComponents[0]) == true) {
            if(is_callable($this->currentContext->get($functionComponents[0]))) {

              // #DEBUG var_dump($variableComponents[0]);
              // #DEBUG var_dump($functionComponents);

              return call_user_func_array(
                $this->currentContext->get($functionComponents[0]),
                $functionComponents[1]
              );
            } else {
              return 'NOT_A_FUNCTION';
            }
          } else {
            return 'FUNCTION_DOES_NOT_EXIST';
          }
        } else {
          // we have a
          if($this->currentContext->exists($variableComponents[0]) == true) {
            return $this->currentContext->get($variableComponents[0]);
          } else {
            return 'UNDEFINED_VARIABLE';
          }
        }
      } else {
          return 'VARIABLE_ERROR';
      }
    }

    /**
     * extract variable components
     * namespace:variablename
     */
    protected function parseVariableComponents($variableString) : array {
      return explode(':', $variableString);
    }

    /**
     * extract function components (name + arguments)
     */
    protected function parseFunctionComponents($functionString) : array {
      $first = explode('(', $functionString);

      // get the function name
      $functionName = $first[0];
      $second = explode(')', $first[1]);

      // comma-separated list of arguments
      $argumentList = $second[0];
      $argumentsParsed = explode(',', $argumentList);

      $arguments = array();

      // only integer args allowed
      foreach($argumentsParsed as $arg) {
        $arguments[] = intval($arg);
      }

      // return as array( functionname, argument-array )
      return array(
        $functionName,
        $arguments
      );
    }

}

class parentheseParser {

  protected $openChar = '{';
  protected $closeChar = '}';

  public function __construct($openChar, $closeChar) {
    $this->openChar = $openChar;
    $this->closeChar = $closeChar;
  }

  protected $buf = array();

  protected function put_to_buf($x) {
      $this->buf[] = $x[0];
      return '@' . (count($this->buf) - 1) . '@';
  }

  protected function get_from_buf($x) {
      return $this->buf[intval($x[1])];
  }

  protected function replace_all($re, $str, $callback) {
      while(preg_match($re, $str))
          $str = preg_replace_callback($re, array($this, $callback), $str);
      return $str;
  }

  public function run($text) : array {
      $this->replace_all('~{[^{}]*}~', $text, 'put_to_buf');
      foreach($this->buf as &$s)
          $s = $this->replace_all('~@(\d+)@~', $s, 'get_from_buf');
      return $this->buf;
  }
}
