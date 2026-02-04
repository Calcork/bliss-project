<?php

namespace App\Controllers\AppController\SystemCall\Testing\Playwright\Tests;

use App\AppCode\Testing\Playwright\PlaywrightTest;
use App\Base\App;
use App\Models\User;

class RegisterEmailTest implements PlaywrightTest
{
    public function __construct(private App $app) {}

    public function generateTestingData(): array
    {
        $unique = bin2hex(random_bytes(4));

        return [
            'TEST_URL' => $this->app->getEnv()['APP_URL'],
            'TEST_EMAIL' => "playwright-{$unique}@test.local",
            'TEST_NAME' => "Test User {$unique}",
            'TEST_PASSWORD' => 'TestPass123!',
            'BYPASS_KEY' => $this->app->getEnv()['BYPASS_KEY'],
            'MAILHOG_API' => 'http://' . $this->app->getEnv()['MAILHOG_HOST'] . ':8025',
        ];
    }

    public function cleanTestingData(array $data): void
    {
        /** @var string $email */
        $email = $data['TEST_EMAIL'];

        $em = $this->app->getDoctrine();
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user !== null) {
            $em->remove($user);
            $em->flush();
        }

        // Delete all Mailhog messages
        /** @var string $mailhog_api */
        $mailhog_api = $data['MAILHOG_API'];
        @file_get_contents($mailhog_api . '/api/v1/messages', false, stream_context_create([
            'http' => ['method' => 'DELETE'],
        ]));
    }

    public function getTestPath(): string
    {
        return 'app/tests/playwright/register-email.spec.js';
    }
}
