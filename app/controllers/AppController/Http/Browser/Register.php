<?php

namespace App\Controllers\AppController\Http\Browser;

use App\AppCode\Actions\Email;
use App\Lib\Http\Inputs;
use App\Models\Language;
use App\Models\User;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Register extends Browser
{

    /** @param list<string> $errors */
    private function renderForm(Request $request, array $errors = [], string $old_name = '', string $old_email = '', int $status = 200): Response
    {
        $html = $this->app->getTemplateMaster()->twigCustomRender('register.twig', [
            'errors' => $errors,
            'old_name' => $old_name,
            'old_email' => $old_email,
            'attributes' => $request->attributes,
        ]);

        return new Response($html, $status);
    }

    function get(Request $request, Found|null $routing_result): Response
    {
        return $this->renderForm($request);
    }

    function post(Request $request, Found|null $routing_result): Response
    {
        $inputs = $this->app->getConfig()['inputs'];
        $validated = Inputs::normalizeAndValidateHttpInputs($request, [
            'name' => $inputs['name'],
            'email' => $inputs['email'],
            'password' => $inputs['password'],
        ], []);

        $translator = $this->app->getTranslator();
        $errors = [];

        foreach (['name', 'email', 'password'] as $field) {
            if (!isset($validated[$field])) {
                $errors[] = $translator->trans("main.register.invalid_{$field}", [], 'en');
            }
        }

        if ($errors !== []) {
            return $this->renderForm($request, $errors, (string) ($validated['name'] ?? ''), (string) ($validated['email'] ?? ''), 422);
        }

        /** @var string $email */
        $email = $validated['email'];
        /** @var string $name */
        $name = $validated['name'];
        /** @var string $password */
        $password = $validated['password'];

        $em = $this->app->getDoctrine();

        if ($em->getRepository(User::class)->findOneBy(['email' => $email]) !== null) {
            return $this->renderForm($request, [$translator->trans('main.register.email_taken_error', [], 'en')], $name, $email, 409);
        }

        $default_language = $em->getRepository(Language::class)->findOneBy(['locale' => $this->app->getConfig()['app']['default_locale']]);

        if ($default_language === null) {
            throw new \RuntimeException('Default language not found.');
        }

        $token = bin2hex(random_bytes(32));
        $user = new User($name, $email, password_hash($password, PASSWORD_BCRYPT), $default_language);
        $user->setEmailVerificationToken($token);
        $em->persist($user);
        $em->flush();

        $app_url = $this->app->getEnv()['APP_URL'];
        $routes = $this->app->getRoutes()->all();
        $verification_url = $app_url . $routes['r|verify-email|GET']->toUri(['token' => $token]);
        (new Email($this->app))->sendVerification($email, $name, $verification_url);

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add('success', $translator->trans('main.login.registration_success', [], 'en'));

        return new RedirectResponse($app_url . $routes['r|login|GET']->toUri(), 302);
    }

}
