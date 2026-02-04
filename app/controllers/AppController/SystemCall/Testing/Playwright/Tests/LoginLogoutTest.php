<?php

namespace App\Controllers\AppController\SystemCall\Testing\Playwright\Tests;

use App\AppCode\Testing\Playwright\PlaywrightTest;
use App\Base\App;
use App\Models\Language;
use App\Models\User;

class LoginLogoutTest implements PlaywrightTest
{

    public function __construct(private App $app) {}

    public function generateTestingData() : array
    {
        $unique = bin2hex(random_bytes(4));
        $password = 'TestPass123!';
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $verified_email = "verified-{$unique}@test.local";
        $unverified_email = "unverified-{$unique}@test.local";

        $em = $this->app->getDoctrine();
        $default_locale = $this->app->getConfig()['app']['default_locale'];
        /** @var Language $language */
        $language = $em->getRepository(Language::class)->findOneBy(['locale' => $default_locale]);

        $verified_user = new User("Verified {$unique}", $verified_email, $password_hash, $language);
        $verified_user->setEmailVerifiedAt(new \DateTimeImmutable());
        $em->persist($verified_user);

        $unverified_user = new User("Unverified {$unique}", $unverified_email, $password_hash, $language);
        $em->persist($unverified_user);

        $em->flush();

        return [
            'TEST_URL' => $this->app->getEnv()['APP_URL'],
            'VERIFIED_EMAIL' => $verified_email,
            'UNVERIFIED_EMAIL' => $unverified_email,
            'PASSWORD' => $password,
            'BYPASS_KEY' => $this->app->getEnv()['BYPASS_KEY'],
        ];
    }

    public function cleanTestingData(array $data): void
    {
        $em = $this->app->getDoctrine();

        foreach (['VERIFIED_EMAIL', 'UNVERIFIED_EMAIL'] as $key) {
            /** @var string $email */
            $email = $data[$key];
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user !== null) {
                $em->remove($user);
            }
        }

        $em->flush();
    }

    public function getTestPath(): string
    {
        return 'app/tests/playwright/login-logout.spec.js';
    }
}
