<?php

namespace Tests\Unit\Controllers;

use Controllers\DatabaseController;
use Controllers\LoginController;
use Models\UserRepository;
use RuntimeException;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    private LoginController $controller;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $db = $this->createInMemoryDatabaseConnection();
        $this->userRepository = new UserRepository($db);
        $this->controller = new LoginController($db, $this->userRepository);
    }

    public function testExecuteRedirectsToDashboardWhenAlreadyLoggedIn(): void
    {
        $_SESSION['user_id'] = 42;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        try {
            $this->controller->execute();
        } finally {
            $this->assertSame('/dashboard', DatabaseController::$lastRedirect);
        }
    }

    public function testGetRequestRendersLoginFormAndGeneratesCsrf(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        try {
            $this->controller->execute();
        } finally {
            $this->assertNotEmpty($_SESSION['csrf_token']);
        }
    }

    public function testPostWithInvalidCsrfTokenShowsSecurityError(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'expected-token';
        $_POST['csrf_token'] = 'invalid-token';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        $this->controller->execute();
    }

    public function testPostWithInvalidCredentialsShowsError(): void
    {
        $this->userRepository->insertUser('user@example.com', 'user', 'CorrectPass123!');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid-csrf';
        $_POST['csrf_token'] = 'valid-csrf';
        $_POST['email'] = 'user@example.com';
        $_POST['password'] = 'WrongPassword';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        try {
            $this->controller->execute();
        } finally {
            $this->assertArrayNotHasKey('user_id', $_SESSION);
        }
    }

    public function testPostWithValidCredentialsLogsInAndRedirects(): void
    {
        $this->userRepository->insertUser('user@example.com', 'user', 'CorrectPass123!');
        $user = $this->userRepository->findByEmail('user@example.com');
        $this->assertNotNull($user);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid-csrf';
        $_POST['csrf_token'] = 'valid-csrf';
        $_POST['email'] = 'user@example.com';
        $_POST['password'] = 'CorrectPass123!';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        try {
            $this->controller->execute();
        } finally {
            $this->assertSame($user->getId(), $_SESSION['user_id']);
            $this->assertSame('/dashboard', DatabaseController::$lastRedirect);
        }
    }
}
