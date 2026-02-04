<?php

namespace App\Tests\Phpunit\Controllers;

use App\Base\App;
use App\Models\Language;
use App\Models\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

abstract class ControllerCase extends TestCase
{

    protected App $app;

    protected function setUp(): void
    {
        $this->app = new App();
        $this->app->getDoctrine()->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $connection = $this->app->getDoctrine()->getConnection();

        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }
    }

    protected function runRequest(Request $request): Response
    {
        $request->headers->set('X-Bypass-Key', $this->app->getEnv()['BYPASS_KEY']);

        // Empty callback prevents Response::send() from outputting during tests
        $response = $this->app->runHttp($request, function (Response $response): void {});

        if ($response === null) {
            throw new \RuntimeException('runHttp returned null — no response was produced.');
        }

        return $response;
    }

    protected function createGetRequest(string $path = '/'): Request
    {
        $request = Request::create($path, 'GET');
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }

    /**
     * @param array<string, string> $body
     */
    protected function createPostRequest(string $path, array $body = []): Request
    {
        $request = Request::create($path, 'POST', $body);
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }

    protected function createUser(string $name = 'Test User', string $email = 'test@example.com', string $password = 'password123', bool $verified = true): User
    {
        $em = $this->app->getDoctrine();

        $language = $em->getRepository(Language::class)->findOneBy(['locale' => 'en']);

        if ($language === null) {
            throw new \RuntimeException('Language "en" not found in database.');
        }

        $user = new User($name, $email, password_hash($password, PASSWORD_BCRYPT), $language);

        if ($verified) {
            $user->setEmailVerifiedAt(new \DateTimeImmutable());
        }

        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function loginUser(Request $request, User $user): void
    {
        $request->getSession()->set('user_id', $user->getId());
    }

}
