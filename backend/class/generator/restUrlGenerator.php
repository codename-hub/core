<?php
namespace codename\core\generator;

/**
 * url generator
 * @package core
 */
class restUrlGenerator implements urlGeneratorInterface{


    /**
     * @inheritDoc
     */
    public function generateFromRoute($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH) {

      $routePartials = explode('/', $name);
      $context = $routePartials[0] ?? null;
      $view = $routePartials[1] ?? null;
      $action = $routePartials[2] ?? null;

      // for now, we're justing doing the basic stuff
      return $this->generateFromParamters(array_merge(
        array(
          'context' => $context,
          'view' => $view,
          'action' => $action
        ),
        $parameters
      ));
    }

    /**
     * @inheritDoc
     */
    public function generateFromParameters($parameters = array(), $referenceType = self::ABSOLUTE_PATH) {

      $components = [];

      if(!empty($parameters['context'])) {
        $components[] = $parameters['context'];
        if(!empty($parameters['view'])) {
          $components[] = $parameters['view'];
          if(!empty($parameters['action'])) {
            $components[] = $parameters['action'];
          }
        }
      }

      unset($parameters['context']);
      unset($parameters['view']);
      unset($parameters['action']);

      $baseUri = implode('/', $components);
      $params = count($parameters) > 0 ? '?'.http_build_query($parameters) : '';
      return "/{$baseUri}{$params}";
    }

}