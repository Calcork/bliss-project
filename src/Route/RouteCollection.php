<?php

namespace Hizech\Bliss\Route;

class RouteCollection
{

    /** @var array<string, Route> */
    private array $routes;

    function __construct()
    {
        $this->routes = [];
    }

    public function add(string $name, Route $route): self
    {
        $this->routes[$name] = $route;
        return $this;
    }

    /**
     * @return array<string, Route>
     */
    public function all(): array
    {
        return $this->routes;
    }

    public function addMany(RouteCollection $routes) : self {
        $this->routes = array_merge($this->routes, $routes->all());
        return $this;
    }

}