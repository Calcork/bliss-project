<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Tailwindcss extends HookFulfiller implements OnContestContext
{
    public function onContestContext(Request $request): Request|Response
    {

        $manager = $this->app->getTailwindcssManager();

        $root = $this->app->getRootPath();
        $sep = DIRECTORY_SEPARATOR;
        $manager->register(
            
            $root . $sep . 'app' . $sep . 'tailwindcss' . $sep . 'main.css',
            $root . $sep . 'public' . $sep . 'css' . $sep . 'main.css',
            [],

        );

        $input_path = $this->app->getRootPath() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'tailwindcss' . DIRECTORY_SEPARATOR . 'main.css';

        if (!$manager->isOutputFresh($input_path)) {
            $manager->build($input_path);
        }

        return $request;

    }
}
