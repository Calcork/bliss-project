<?php

namespace App\Controllers\AppController\SystemCall\Testing\Playwright\Tests;

use App\AppCode\Testing\Playwright\PlaywrightTest;
use App\Base\App;
use App\Models\Language;
use App\Models\User;

class CheckEmailAvailabilityTest implements PlaywrightTest
{
    public function __construct(private App $app) {}

    public function generateTestingData(): array
    {
        $unique = bin2hex(random_bytes(4));
        $taken_email = "taken-{$unique}@test.local";

        $em = $this->app->getDoctrine();
        $default_locale = $this->app->getConfig()['app']['default_locale'];
        /** @var Language $language */
        $language = $em->getRepository(Language::class)->findOneBy(['locale' => $default_locale]);
        $user = new User("Existing User {$unique}", $taken_email, password_hash('password', PASSWORD_DEFAULT), $language);
        $em->persist($user);
        $em->flush();

        return [
            'TEST_URL' => $this->app->getEnv()['APP_URL'],
            'TAKEN_EMAIL' => $taken_email,
            'FRESH_EMAIL' => "fresh-{$unique}@test.local",
            'BYPASS_KEY' => $this->app->getEnv()['BYPASS_KEY'],
        ];
    }

    public function cleanTestingData(array $data): void
    {
        /** @var string $email */
        $email = $data['TAKEN_EMAIL'];

        $em = $this->app->getDoctrine();
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user !== null) {
            $em->remove($user);
            $em->flush();
        }
    }

    public function getTestPath(): string
    {
        return 'app/tests/playwright/check-email-availability.spec.js';
    }
}
