<?php

namespace App\Base;

use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\OnContestHttpRoutes;
use Hizech\Bliss\App\HookFulfillers\OnContestSystemcallAliases;
use Hizech\Bliss\Route\RouteCollection;
use Hizech\Bliss\Controller\ControllerHandler;

class GenericFulfillers implements OnContestHttpRoutes, OnContestSystemcallAliases
{

   function onContestHttpRoutes(App $app, RouteCollection $routes) : RouteCollection
   {
       $new_collection = new RouteCollection();

       $routes->addMany($new_collection);

       return $routes;

   }


    /**
     * @param array<string, array<string, ControllerHandler>> $systemcall_aliases
     * @return array<string, array<string, ControllerHandler>>
     */
    function onContestSystemcallAliases(App $app, array $systemcall_aliases): array
    {
        return array_merge($systemcall_aliases, []);
    }

}