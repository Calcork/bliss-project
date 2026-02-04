<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use App\Lib\TemplateMaster\TranslationExtension;
use App\Models\Language;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DecideUserDetails extends HookFulfiller implements OnContestContext
{
    public function onContestContext(Request $request): Request|Response
    {
        $default_locale = $this->app->getConfig()['app']['default_locale'];
        $cookie_locale = $request->cookies->get('locale');

        $locale = $default_locale;

        if (is_string($cookie_locale) && $cookie_locale !== '') {
            $language = $this->app->getDoctrine()
                ->getRepository(Language::class)
                ->findOneBy(['locale' => $cookie_locale]);

            if ($language !== null) {
                $locale = $cookie_locale;
            }
        }

        $rtl_locales = ['he', 'ar', 'fa', 'ur'];

        $request->attributes->set('app_url', $this->app->getEnv()['APP_URL']);
        $request->attributes->set('browser_locale', $locale);
        $request->attributes->set('text_dir', in_array($locale, $rtl_locales, true) ? 'rtl' : 'ltr');

        /** @var TranslationExtension $translation_extension */
        $translation_extension = $this->app->getTemplateMaster()
            ->getTwig()
            ->getExtension(TranslationExtension::class);

        $translation_extension->setDefaultLocale($locale);

        $set_locale_route = $this->app->getRoutes()->all()['r|set-locale|GET'];
        $app_url = $this->app->getEnv()['APP_URL'];

        $languages = $this->app->getDoctrine()
            ->getRepository(Language::class)
            ->findAll();

        $locale_links = [];
        foreach ($languages as $lang) {
            $locale_links[] = [
                'locale' => $lang->getLocale(),
                'name' => $lang->getName(),
                'url' => $app_url . $set_locale_route->toUri(['locale' => $lang->getLocale()]),
                'active' => $lang->getLocale() === $locale,
            ];
        }

        $request->attributes->set('locale_links', $locale_links);

        return $request;
    }
}
