<?php

namespace App\Controllers\AppController\Http\Browser;

use App\Models\Language;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale extends Browser
{

    function get(Request $request, Found|null $routing_result): Response
    {
        $locale = $routing_result?->params['locale'] ?? '';

        $language = $this->app->getDoctrine()
            ->getRepository(Language::class)
            ->findOneBy(['locale' => $locale]);

        $referer = $request->headers->get('referer');
        $app_url = $this->app->getEnv()['APP_URL'];
        $redirect_url = is_string($referer) && str_starts_with($referer, $app_url) ? $referer : $app_url;

        $response = new RedirectResponse($redirect_url, 302);

        if ($language !== null) {
            $cookie = Cookie::create('locale')
                ->withValue($locale)
                ->withPath('/')
                ->withHttpOnly(false)
                ->withSameSite('lax');

            $response->headers->setCookie($cookie);
        }

        return $response;
    }

}
