<?php
namespace codename\core\generator;

/**
 * url generator
 * @package core
 */
class urlGenerator implements urlGeneratorInterface{


    /**
     * @inheritDoc
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH) {

      $routePartials = explode('/', $name);
      $context = $routePartials[0] ?? null;
      $view = $routePartials[1] ?? null;
      $action = $routePartials[2] ?? null;

      // for now, we're justing doing the basic stuff
      return '/?' . http_build_query(array_merge(
        array(
          'context' => $context,
          'view' => $view,
          'action' => $action
        ),
        $parameters
      ));

    }

}