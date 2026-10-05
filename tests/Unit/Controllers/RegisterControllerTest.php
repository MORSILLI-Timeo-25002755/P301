<?php

namespace Tests\Unit\Controllers;

use Controllers\DatabaseController;
use Controllers\RegisterController;
use Models\UserRepository;
use RuntimeException;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    private RegisterController $controller;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $db = $this->createInMemoryDatabaseConnection();
        $this->userRepository = new UserRepository($db);
        $this->controller = new RegisterController($db, $this->userRepository);
    }

    public function testGetRequestRendersFormAndGeneratesCsrfToken(): void
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
        $_SESSION['csrf_token'] = 'real-token';
        $_POST['csrf_token'] = 'wrong-token';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        $this->controller->execute();
    }

    public function testPostWithEmptyFieldsShowsValidationError(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'valid-token';
        $_POST['email'] = '';
        $_POST['username'] = 'john';
        $_POST['pwd'] = 'secret123';
        $_POST['conf'] = 'secret123';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        $this->controller->execute();
    }

    public function testPostWithPasswordMismatchShowsPasswordError(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'valid-token';
        $_POST['email'] = 'john@example.com';
        $_POST['username'] = 'john';
        $_POST['pwd'] = 'secret123';
        $_POST['conf'] = 'different_password';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        $this->controller->execute();
    }

    public function testPostWithValidDataInsertsUserAndRedirectsToLogin(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'valid-token';
        $_POST['email'] = 'newuser@example.com';
        $_POST['username'] = 'newuser';
        $_POST['pwd'] = 'SecretPass123!';
        $_POST['conf'] = 'SecretPass123!';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        try {
            $this->controller->execute();
        } finally {
            $this->assertSame('/login', DatabaseController::$lastRedirect);
            $created = $this->userRepository->findByEmail('newuser@example.com');
            $this->assertNotNull($created);
            $this->assertSame('newuser', $created->getUsername());
        }
    }

    public function testPostWithDuplicateEmailShowsError(): void
    {
        $this->userRepository->insertUser('existing@example.com', 'existing', 'pass');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid-token';
        $_POST['csrf_token'] = 'valid-token';
        $_POST['email'] = 'existing@example.com';
        $_POST['username'] = 'anothername';
        $_POST['pwd'] = 'SecretPass123!';
        $_POST['conf'] = 'SecretPass123!';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        $this->controller->execute();
    }
}
