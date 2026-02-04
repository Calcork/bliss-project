<?php

namespace App\Controllers\AppController\Http\Browser;

use App\AppCode\Actions\Email;
use App\Lib\Http\Inputs;
use App\Models\User;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ForgotPassword extends Browser
{

    /** @param list<string> $errors */
    private function renderForm(Request $request, array $errors = [], string $old_email = '', int $status = 200): Response
    {
        $html = $this->app->getTemplateMaster()->twigCustomRender('forgot-password.twig', [
            'errors' => $errors,
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
            'email' => $inputs['email'],
        ], []);

        $translator = $this->app->getTranslator();

        if (!isset($validated['email'])) {
            return $this->renderForm($request, [$translator->trans('main.forgot_password.invalid_email', [], 'en')], '', 422);
        }

        /** @var string $email */
        $email = $validated['email'];

        $em = $this->app->getDoctrine();
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        $app_url = $this->app->getEnv()['APP_URL'];
        $routes = $this->app->getRoutes()->all();

        if ($user !== null) {
            $token = bin2hex(random_bytes(32));
            $user->setPasswordResetToken($token);
            $user->setPasswordResetExpiresAt(new \DateTimeImmutable('+1 hour'));
            $em->flush();

            $reset_url = $app_url . $routes['r|reset-password|GET']->toUri(['token' => $token]);
            (new Email($this->app))->sendPasswordReset($email, $user->getName(), $reset_url);
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add('success', $translator->trans('main.forgot_password.success', [], 'en'));

        return new RedirectResponse($app_url . $routes['r|login|GET']->toUri(), 302);
    }

}
