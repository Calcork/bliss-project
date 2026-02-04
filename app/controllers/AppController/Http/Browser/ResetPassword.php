<?php

namespace App\Controllers\AppController\Http\Browser;

use App\Lib\Http\Inputs;
use App\Models\User;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResetPassword extends Browser
{

    /** @param list<string> $errors */
    private function renderForm(Request $request, string $token, array $errors = [], int $status = 200): Response
    {
        $html = $this->app->getTemplateMaster()->twigCustomRender('reset-password.twig', [
            'token' => $token,
            'errors' => $errors,
            'attributes' => $request->attributes,
        ]);

        return new Response($html, $status);
    }

    function get(Request $request, Found|null $routing_result): Response
    {
        $token = $routing_result?->params['token'] ?? null;
        $translator = $this->app->getTranslator();

        if ($token === null) {
            return new Response($translator->trans('main.reset_password.invalid_token', [], 'en'), 400);
        }

        $user = $this->findValidUser($token);

        if ($user === null) {
            return new Response($translator->trans('main.reset_password.invalid_token', [], 'en'), 400);
        }

        return $this->renderForm($request, $token);
    }

    function post(Request $request, Found|null $routing_result): Response
    {
        $inputs = $this->app->getConfig()['inputs'];
        $translator = $this->app->getTranslator();

        $token = $request->request->get('token');

        if (!is_string($token) || $token === '') {
            return new Response($translator->trans('main.reset_password.invalid_token', [], 'en'), 400);
        }

        $user = $this->findValidUser($token);

        if ($user === null) {
            return new Response($translator->trans('main.reset_password.invalid_token', [], 'en'), 400);
        }

        $validated = Inputs::normalizeAndValidateHttpInputs($request, [
            'password' => $inputs['password'],
        ], []);

        if (!isset($validated['password'])) {
            return $this->renderForm($request, $token, [$translator->trans('main.reset_password.invalid_password', [], 'en')], 422);
        }

        /** @var string $password */
        $password = $validated['password'];

        $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
        $user->setPasswordResetToken(null);
        $user->setPasswordResetExpiresAt(null);
        $this->app->getDoctrine()->flush();

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add('success', $translator->trans('main.reset_password.success', [], 'en'));

        $app_url = $this->app->getEnv()['APP_URL'];

        return new RedirectResponse($app_url . $this->app->getRoutes()->all()['r|login|GET']->toUri(), 302);
    }

    private function findValidUser(string $token): ?User
    {
        $em = $this->app->getDoctrine();
        $user = $em->getRepository(User::class)->findOneBy(['password_reset_token' => $token]);

        if ($user === null) {
            return null;
        }

        $expires_at = $user->getPasswordResetExpiresAt();

        if ($expires_at === null || $expires_at < new \DateTimeImmutable()) {
            return null;
        }

        return $user;
    }

}
