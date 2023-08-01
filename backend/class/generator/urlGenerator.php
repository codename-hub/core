<?php

namespace codename\core\generator;

/**
 * url generator
 * @package core
 */
class urlGenerator implements urlGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generateFromRoute(string $name, $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        $routePartials = explode('/', $name);
        $context = $routePartials[0] ?? null;
        $view = $routePartials[1] ?? null;
        $action = $routePartials[2] ?? null;

        // for now, we're justin doing the basic stuff
        return $this->generateFromParameters(
            array_merge(
                [
                  'context' => $context,
                  'view' => $view,
                  'action' => $action,
                ],
                $parameters
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function generateFromParameters($parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        // for now, we're justin doing the basic stuff
        return '/?' . http_build_query(
            $parameters
        );
    }
}
