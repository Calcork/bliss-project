<?php

namespace App\Tests\Phpunit\Controllers\Http\Browser;

use App\Tests\Phpunit\Controllers\ControllerCase;

class DashboardTest extends ControllerCase
{
    function testGetRedirectsWhenNotLoggedIn(): void
    {
        $response = $this->runRequest($this->createGetRequest('/dashboard'));

        $this->assertEquals(302, $response->getStatusCode());
    }

    function testGetRendersWhenLoggedIn(): void
    {
        $user = $this->createUser('Dashboard User', 'dash@example.com', 'password123');
        $request = $this->createGetRequest('/dashboard');
        $this->loginUser($request, $user);

        $response = $this->runRequest($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    function testPostRedirectsWhenNotLoggedIn(): void
    {
        $response = $this->runRequest($this->createPostRequest('/dashboard', [
            'name' => 'New Name',
        ]));

        $this->assertEquals(302, $response->getStatusCode());
    }

    function testPostUpdateName(): void
    {
        $user = $this->createUser('Old Name', 'dashpost@example.com', 'password123');
        $request = $this->createPostRequest('/dashboard', ['name' => 'New Name']);
        $this->loginUser($request, $user);

        $response = $this->runRequest($request);

        $this->assertEquals(302, $response->getStatusCode());
    }

    function testPostUpdateWithInvalidName(): void
    {
        $user = $this->createUser('Valid Name', 'dashbad@example.com', 'password123');
        $request = $this->createPostRequest('/dashboard', ['name' => str_repeat('a', 101)]);
        $this->loginUser($request, $user);

        $response = $this->runRequest($request);

        $this->assertEquals(422, $response->getStatusCode());
    }

    function testPostUpdatePassword(): void
    {
        $user = $this->createUser('Pw User', 'dashpw@example.com', 'password123');
        $request = $this->createPostRequest('/dashboard', [
            'current_password' => 'password123',
            'new_password' => 'newpassword123',
        ]);
        $this->loginUser($request, $user);

        $response = $this->runRequest($request);

        $this->assertEquals(302, $response->getStatusCode());
    }

    function testPostUpdatePasswordWithWrongCurrent(): void
    {
        $user = $this->createUser('Pw Fail', 'dashpwfail@example.com', 'password123');
        $request = $this->createPostRequest('/dashboard', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
        ]);
        $this->loginUser($request, $user);

        $response = $this->runRequest($request);

        $this->assertEquals(422, $response->getStatusCode());
    }

}
