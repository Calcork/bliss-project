<?php

namespace App\Base;

class App extends \Hizech\Bliss\App\App
{

    private GenericFulfillers $generic_fulfillers;
    private HttpFulfillers $http_fulfillers;
    private SystemcallFulfillers $systemcall_fulfillers;

    private function getGenericFulfillers() : GenericFulfillers
    {
        if(isset($this->generic_fulfillers)) return $this->generic_fulfillers;
        else {
            $this->generic_fulfillers = new GenericFulfillers();
            return $this->generic_fulfillers;
        }
    }

    private function getHttpFulfillers() : HttpFulfillers {
        if(isset($this->http_fulfillers)) return $this->http_fulfillers;
        else {
            $this->http_fulfillers = new HttpFulfillers();
            return $this->http_fulfillers;
        }
    }

    private function getSystemcallFulfillers() : SystemcallFulfillers {
        if(isset($this->systemcall_fulfillers)) return $this->systemcall_fulfillers;
        else {
            $this->systemcall_fulfillers = new SystemcallFulfillers();
            return $this->systemcall_fulfillers;
        }
    }

    protected function onContestHttpRoutesFulfillers(): array
    {
        $ar = [
            'root' => $this->getGenericFulfillers(),
        ];
        return array_merge(parent::onContestHttpRoutesFulfillers(), $ar);
    }

    protected function onContestSystemcallAliasesFulfillers(): array
    {

        $ar = [
            'root' => $this->getGenericFulfillers(),
        ];
        return array_merge(parent::onContestSystemcallAliasesFulfillers(), $ar);

    }

    protected function onHttpNotFoundFulfillers(): array
    {
        $ar = [
            'root' => $this->getHttpFulfillers(),
        ];
        return array_merge(parent::onHttpNotFoundFulfillers(), $ar);
    }

    protected function onSystemcallNotFoundFulfillers(): array
    {
        $ar = [
            'root' => $this->getSystemcallFulfillers(),
        ];
        return array_merge(parent::onSystemcallNotFoundFulfillers(), $ar);
    }

}