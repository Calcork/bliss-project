<?php

namespace App\Base;

use App\Base\HttpFulfillers\OnContestContextFulfillers\AdminKey;
use App\Base\HttpFulfillers\OnContestContextFulfillers\NormalizeContext;
use App\Base\HttpFulfillers\OnNotFoundFulfillers\BaseNotFound;
use App\Base\HttpFulfillers\OnContestContextFulfillers\BaseContext as HttpBaseContext;
use App\Base\HttpFulfillers\OnContestContextFulfillers\RedirectSlash;
use App\Base\HttpFulfillers\OnContestControllerFulfillers\BaseController as HttpBaseController;
use App\Base\HttpFulfillers\OnContestResponseFulfillers\BaseResponse;
use App\Base\HttpFulfillers\OnAfterResponseSentFulfillers\BaseAfterResponseSent;
use App\Base\SystemcallFulfillers\OnContestContextFulfillers\BaseContext as SystemcallBaseContext;
use App\Base\SystemcallFulfillers\OnContestControllerFulfillers\BaseController as SystemcallBaseController;
use App\Base\SystemcallFulfillers\OnAfterControllerRunFulfillers\BaseAfterControllerRun;

class App extends \Hizech\Bliss\App\App
{
    /**
     * @var array<string, mixed> $config
     */
    private array $config;

    /**
     * @return array<string, mixed>
     */
    function getConfig() : array {

        if(isset($this->config)) return $this->config;

        else {

            $config = [];

            $config['migrations'] = require($this->getRootPath() . I . 'app' . I . 'config' . I . 'migrations.php');

            $this->config = $config;
            return $this->config;

        }

    }

    public function getStorageCacheFolders(): array
    {

        $new_folders = [

        ];

        return array_merge(parent::getStorageCacheFolders(), $new_folders);

    }

    public function getStorageFolders(): array
    {
        $new_folders = [

        ];

        return array_merge(parent::getStorageFolders(), $new_folders);
    }


    protected function onHttpContestContextFulfillers(): array
    {
        $ar = [
            'BaseContext' => new HttpBaseContext(),
            'RedirectSlash' => new RedirectSlash(),
            'NormalizeContext' => new NormalizeContext(),
            'AdminKey' => new AdminKey(),
            'RateLimit' => new \App\Base\HttpFulfillers\OnContestContextFulfillers\RateLimit(),
        ];
        return array_merge(parent::onHttpContestContextFulfillers(), $ar);
    }

    protected function onHttpContestControllerFulfillers(): array
    {
        $ar = [
            'BaseController' => new HttpBaseController(),
        ];
        return array_merge(parent::onHttpContestControllerFulfillers(), $ar);
    }

    protected function onHttpContestResponseFulfillers(): array
    {
        $ar = [
            'BaseResponse' => new BaseResponse(),
        ];
        return array_merge(parent::onHttpContestResponseFulfillers(), $ar);
    }

    protected function onHttpNotFoundFulfillers(): array
    {
        $ar = [
            'BaseNotFound' => new BaseNotFound(),
        ];
        return array_merge(parent::onHttpNotFoundFulfillers(), $ar);
    }

    protected function onHttpAfterResponseSentFulfillers(): array
    {
        $ar = [
            'BaseAfterResponseSent' => new BaseAfterResponseSent(),
        ];
        return array_merge(parent::onHttpAfterResponseSentFulfillers(), $ar);
    }

    protected function onSystemcallContestContextFulfillers(): array
    {
        $ar = [
            'BaseContext' => new SystemcallBaseContext(),
        ];
        return array_merge(parent::onSystemcallContestContextFulfillers(), $ar);
    }

    protected function onSystemcallContestControllerFulfillers(): array
    {
        $ar = [
            'BaseController' => new SystemcallBaseController(),
        ];
        return array_merge(parent::onSystemcallContestControllerFulfillers(), $ar);
    }

    protected function onSystemcallNotFoundFulfillers(): array
    {
        $ar = [
            'BaseNotFound' => new \App\Base\SystemcallFulfillers\OnNotFoundFulfillers\BaseNotFound(),
        ];
        return array_merge(parent::onSystemcallNotFoundFulfillers(), $ar);
    }

    protected function onSystemcallAfterControllerRunFulfillers(): array
    {
        $ar = [
            'BaseAfterControllerRun' => new BaseAfterControllerRun(),
        ];
        return array_merge(parent::onSystemcallAfterControllerRunFulfillers(), $ar);
    }

}
