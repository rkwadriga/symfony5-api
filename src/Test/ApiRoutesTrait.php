<?php
/**
 * Created 2021-10-17
 * Author Dmitry Kushneriov
 */

namespace App\Test;

trait ApiRoutesTrait
{
    protected function getRequestMethod(string $routeName): string
    {
        $methods = $this->getRouter()->getRouteCollection()->get($routeName)->getMethods();
        return array_shift($methods);
    }

    protected function getRequestUri(string $routeName, array $parameters = []): string
    {
        return $this->getRouter()->generate($routeName, $parameters);
    }

    protected function getRequestAsString(string $routeName, array $parameters = []): string
    {
        return $this->getRequestMethod($routeName) . ' ' . $this->getRequestUri($routeName, $parameters);
    }
}