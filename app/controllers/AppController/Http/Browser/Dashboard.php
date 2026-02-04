<?php

namespace App\Controllers\AppController\Http\Browser;

use App\Lib\Http\Inputs;
use App\Models\User;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Dashboard extends Browser
{

    private function getUser(Request $request): ?User
    {
        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();

        if (!$session->has('user_id')) {
            return null;
        }

        /** @var int $user_id */
        $user_id = $session->get('user_id');

        return $this->app->getDoctrine()->getRepository(User::class)->find($user_id);
    }

    private function redirect(string $route_name): RedirectResponse
    {
        $app_url = $this->app->getEnv()['APP_URL'];

        return new RedirectResponse($app_url . $this->app->getRoutes()->all()[$route_name]->toUri(), 302);
    }

    /** @param list<string> $errors */
    private function renderDashboard(Request $request, User $user, array $errors = [], ?string $success = null): Response
    {
        $html = $this->app->getTemplateMaster()->twigCustomRender('dashboard.twig', [
            'user' => $user,
            'errors' => $errors,
            'success' => $success,
            'attributes' => $request->attributes,
        ]);

        return new Response($html, $errors !== [] ? 422 : 200);
    }

    function get(Request $request, Found|null $routing_result): Response
    {
        $user = $this->getUser($request);

        if ($user === null) {
            return $this->redirect('r|login|GET');
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        $flashes = $session->getFlashBag()->get('success');

        return $this->renderDashboard($request, $user, [], $flashes[0] ?? null);
    }

    function post(Request $request, Found|null $routing_result): Response
    {
        $user = $this->getUser($request);

        if ($user === null) {
            return $this->redirect('r|login|GET');
        }

        $inputs = $this->app->getConfig()['inputs'];
        $translator = $this->app->getTranslator();
        $errors = [];
        $changed = false;

        $profile_validated = Inputs::normalizeAndValidateHttpInputs($request, [
            'name' => $inputs['name'],
            'email' => $inputs['email'],
        ], []);

        $password_validated = Inputs::normalizeAndValidateHttpInputs($request, [
            'current_password' => $inputs['password'],
            'new_password' => $inputs['password'],
        ], []);

        // Name update
        $raw_name = $request->request->get('name');
        if ($raw_name !== null && $raw_name !== '') {
            if (!isset($profile_validated['name'])) {
                $errors[] = $translator->trans('main.dashboard.invalid_name', [], 'en');
            } else {
                /** @var string $name */
                $name = $profile_validated['name'];
                if ($name !== $user->getName()) {
                    $user->setName($name);
                    $changed = true;
                }
            }
        }

        // Email update
        $raw_email = $request->request->get('email');
        if ($raw_email !== null && $raw_email !== '') {
            if (!isset($profile_validated['email'])) {
                $errors[] = $translator->trans('main.dashboard.invalid_email', [], 'en');
            } else {
                /** @var string $email */
                $email = $profile_validated['email'];
                if ($email !== $user->getEmail()) {
                    $existing = $this->app->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);
                    if ($existing !== null) {
                        $errors[] = $translator->trans('main.dashboard.email_taken', [], 'en');
                    } else {
                        $user->setEmail($email);
                        $changed = true;
                    }
                }
            }
        }

        // Password update
        $raw_current = $request->request->get('current_password');
        $raw_new = $request->request->get('new_password');
        if (($raw_current !== null && $raw_current !== '') || ($raw_new !== null && $raw_new !== '')) {
            if (!isset($password_validated['current_password'])) {
                $errors[] = $translator->trans('main.dashboard.invalid_current_password', [], 'en');
            } elseif (!isset($password_validated['new_password'])) {
                $errors[] = $translator->trans('main.dashboard.invalid_new_password', [], 'en');
            } else {
                /** @var string $current_password */
                $current_password = $password_validated['current_password'];
                /** @var string $new_password */
                $new_password = $password_validated['new_password'];

                if (!password_verify($current_password, $user->getPasswordHash())) {
                    $errors[] = $translator->trans('main.dashboard.wrong_current_password', [], 'en');
                } else {
                    $user->setPasswordHash(password_hash($new_password, PASSWORD_BCRYPT));
                    $changed = true;
                }
            }
        }

        if ($errors !== []) {
            return $this->renderDashboard($request, $user, $errors);
        }

        if ($changed) {
            $this->app->getDoctrine()->flush();
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add('success', $translator->trans('main.dashboard.success', [], 'en'));

        return $this->redirect('r|dashboard|GET');
    }

}
