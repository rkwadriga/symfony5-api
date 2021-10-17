<?php
/**
 * Created 2021-10-17
 * Author Dmitry Kushneriov
 */

namespace App\Test;

trait ApiRoutesTrait
{
    protected function getRequestMethod(mixed $routeName): string
    {
        $routeName = $this->parseRouteName($routeName)[0];
        $methods = $this->getRouter()->getRouteCollection()->get($routeName)->getMethods();
        return array_shift($methods);
    }

    protected function getRequestUri(mixed $routeName): string
    {
        [$routeName, $parameters] = $this->parseRouteName($routeName);
        return $this->getRouter()->generate($routeName, $parameters);
    }

    protected function getRequestAsString(mixed $routeName): string
    {
        return $this->getRequestMethod($routeName) . ' ' . $this->getRequestUri($routeName);
    }

    private function parseRouteName(mixed $routeName): array
    {
        if (is_array($routeName) && count($routeName) === 2) {
            [$routeName, $parameters] = $routeName;
        } else {
            $parameters = [];
        }
        if (is_scalar($parameters)) {
            $parameters = ['id' => $parameters];
        }

        if (!is_string($routeName) || !is_array($parameters)) {
            throw new \ErrorException(sprintf('Invalid route given: "%s:', is_scalar($routeName) ? $routeName : json_encode($routeName)));
        }

        return [$routeName, $parameters];
    }
}