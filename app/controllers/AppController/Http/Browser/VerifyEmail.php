<?php

namespace App\Controllers\AppController\Http\Browser;

use App\Models\User;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyEmail extends Browser
{

    function get(Request $request, Found|null $routing_result): Response
    {
        $token = $routing_result?->params['token'] ?? null;

        if ($token === null) {
            return new Response($this->app->getTranslator()->trans('main.verify_email.invalid_token', [], 'en'), 400);
        }

        $em = $this->app->getDoctrine();
        $user = $em->getRepository(User::class)->findOneBy(['email_verification_token' => $token]);

        if ($user === null) {
            return new Response($this->app->getTranslator()->trans('main.verify_email.invalid_token', [], 'en'), 400);
        }

        if ($user->isEmailVerified()) {
            return new Response($this->app->getTranslator()->trans('main.verify_email.already_verified', [], 'en'), 400);
        }

        $user->setEmailVerifiedAt(new \DateTimeImmutable());
        $user->setEmailVerificationToken(null);
        $em->flush();

        $app_url = $this->app->getEnv()['APP_URL'];
        $login_url = $app_url . $this->app->getRoutes()->all()['r|login|GET']->toUri();

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add('success', $this->app->getTranslator()->trans('main.login.email_verified', [], 'en'));

        return new RedirectResponse($login_url, 302);
    }

}
