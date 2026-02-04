<?php

namespace App\Controllers\AppController\Http\Browser;

use App\Lib\Http\Inputs;
use App\Models\User;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Login extends Browser
{

    /** @param list<string> $errors */
    private function renderForm(Request $request, array $errors = [], string $old_email = '', ?string $success_message = null, int $status = 200): Response
    {
        $html = $this->app->getTemplateMaster()->twigCustomRender('login.twig', [
            'errors' => $errors,
            'old_email' => $old_email,
            'success_message' => $success_message,
            'attributes' => $request->attributes,
        ]);

        return new Response($html, $status);
    }

    function get(Request $request, Found|null $routing_result): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        $flashes = $session->getFlashBag()->get('success');

        return $this->renderForm($request, [], '', $flashes[0] ?? null);
    }

    function post(Request $request, Found|null $routing_result): Response
    {
        $inputs = $this->app->getConfig()['inputs'];
        $validated = Inputs::normalizeAndValidateHttpInputs($request, [
            'email' => $inputs['email'],
            'password' => $inputs['password'],
        ], []);

        $translator = $this->app->getTranslator();
        $errors = [];

        foreach (['email', 'password'] as $field) {
            if (!isset($validated[$field])) {
                $errors[] = $translator->trans("main.login.invalid_{$field}", [], 'en');
            }
        }

        if ($errors !== []) {
            return $this->renderForm($request, $errors, (string) ($validated['email'] ?? ''), null, 422);
        }

        /** @var string $email */
        $email = $validated['email'];
        /** @var string $password */
        $password = $validated['password'];

        $user = $this->app->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user === null || !password_verify($password, $user->getPasswordHash())) {
            return $this->renderForm($request, [$translator->trans('main.login.invalid_credentials', [], 'en')], $email, null, 401);
        }

        $config = $this->app->getConfig()['app'];
        if ($config['require_email_confirmation'] && !$user->isEmailVerified()) {
            return $this->renderForm($request, [$translator->trans('main.login.unverified_email', [], 'en')], $email, null, 403);
        }

        $request->getSession()->set('user_id', $user->getId());
        $request->getSession()->migrate(true);

        $app_url = $this->app->getEnv()['APP_URL'];

        return new RedirectResponse($app_url . $this->app->getRoutes()->all()['r|GET']->toUri(), 302);
    }

}
