<?php

namespace Hizech\Bliss\Route;

class RouteCollection
{

    /** @var array<string, Route|RouteCollection> $items */
    private array $items;

    /**
     * @param array<int, string> $levels
     * @param  array<string, \Hizech\Bliss\Route\Parameter\Parameter> $parameters
     * @param array<int, string> $tags,
     */
    function __construct(

        private array $levels = [],
        private array $parameters = [],
        private array $tags = [],

    )
    {
        $this->items = [];
    }

    public function add(string $name, Route|RouteCollection $item): void
    {
        $this->items[$name] = $item;
    }

    /** @param array<string, Route|RouteCollection> $items */
    public function addMany(array $items): self
    {
        foreach ($items as $name => $item) {
            $this->items[$name] = $item;
        }

        return $this;
    }

    /**
     * @return array<string, Route>
     */
    public function allLinearRoutes() : array {

        $routes = [];

        foreach ($this->items as $name => $item) {

            if($item instanceof Route) {

                $new_route = new Route(

                    array_merge($this->levels, $item->levels),
                    $item->controller_handler,
                    $item->http_methods,
                    array_merge($this->parameters, $item->parameters),
                    array_merge($this->tags, $item->tags),

                );

                $routes[$name] = $new_route;

            }
            elseif($item instanceof RouteCollection) {

                $r_routes = $item->allLinearRoutes();

                foreach($r_routes as $r_name => $r_item) {

                    $routes[$name . '|' . $r_name] = new Route(
                        array_merge($this->levels, $r_item->levels),
                        $r_item->controller_handler,
                        $r_item->http_methods,
                        array_merge($this->parameters, $r_item->parameters),
                        array_merge($this->tags, $r_item->tags),

                    );
                }

            }
            else {
                throw new \LogicException('Not possible');
            }
        }

        return $routes;

    }

}