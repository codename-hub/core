<?php
namespace codename\core\request;

/**
 * I handle all the data for a CLI request
 * @package core
 * @since 2017-10-12
 */
class cli extends \codename\core\request {

    /**
     * @inheritDoc
     */
    public function __CONSTRUCT()
    {
      parent::__construct();

      // make $argv available globally (in this ctor)
      global $argv;

      // parse commandline arguments
      // --command=value
      $cliData = array();
      foreach ($argv as $arg) {
        $e = explode("=", $arg);
        if(stripos($e[0], '--') === 0) {
          $param = substr($e[0], 2);

          $groups = array();
          $matches = preg_match_all("/(?:\\[([\\w .!?]+)\\]+|(\\w+))/", $param, $groups);

          $fullparam = array($groups[2][0]);
          $arraykeys = array_slice($groups[1], 1);
          $fullparam = array_merge($fullparam, $arraykeys);

          /**
           * we're creating the cli request array recursively
           * to allow
           *
           * --arg[arrkey1][arrkey2]=123
           *
           * resulting in
           *
           * array(
           *  'arg' => array(
           *    'arrkey1' => array(
           *      'arrkey2' => 123
           *    )
           *  )
           * )
           *
           */

          $rec = &$cliData;

          if(strlen($param) > 0) {
            foreach($fullparam as $p) {
              if(!array_key_exists($p, $rec)) {
                // if it's the last param we're parsing, simply set value
                if($p == end($fullparam)) {
                  $rec[$p] = $e[1] ?? null;
                  break;
                } else {
                  // begin creating a new sub-structure
                  $rec[$p] = array();
                }
              } else {
                // if it's the last param we're parsing, simply set value
                if($p == end($fullparam)) {
                  $rec[$p] = $e[1] ?? null;
                  break;
                }
              }

              // dig deeper
              $rec = &$rec[$p];
            }
          }

        }
      }

      $this->addData($cliData);
      $this->setData('lang', "de_DE");
      return $this;
    }

}

