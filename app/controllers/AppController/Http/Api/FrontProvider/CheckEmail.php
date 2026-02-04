<?php

namespace App\Controllers\AppController\Http\Api\FrontProvider;

use App\Lib\Http\Inputs;
use App\Models\User;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEmail extends FrontProvider
{

    function get(Request $request, Found|null $routing_result): Response
    {
        $inputs = $this->app->getConfig()['inputs'];
        $validated = Inputs::normalizeAndValidateHttpInputs($request, ['email' => $inputs['email']], []);

        if (!isset($validated['email'])) {
            return new JsonResponse(['available' => false], 422);
        }

        /** @var string $email */
        $email = $validated['email'];

        $existing = $this->app->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        return new JsonResponse(['available' => $existing === null]);
    }

}
