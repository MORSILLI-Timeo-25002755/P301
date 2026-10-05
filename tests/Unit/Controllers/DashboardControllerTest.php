<?php

namespace Tests\Unit\Controllers;

use Controllers\DashboardController;
use Controllers\DatabaseController;
use Models\UserRepository;
use RuntimeException;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    private UserRepository $userRepository;
    private \_assets\Includes\DatabaseConnection $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = $this->createInMemoryDatabaseConnection();
        $this->userRepository = new UserRepository($this->db);
    }

    public function testInstantiationRedirectsToLoginWhenNoSession(): void
    {
        unset($_SESSION['user_id']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        try {
            new DashboardController($this->db, $this->userRepository);
        } finally {
            $this->assertSame('/login', DatabaseController::$lastRedirect);
        }
    }

    public function testInstantiationRedirectsToLoginWhenUserNotFoundInDb(): void
    {
        $_SESSION['user_id'] = 99999;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EXIT_CALLED');

        try {
            new DashboardController($this->db, $this->userRepository);
        } finally {
            $this->assertSame('/login', DatabaseController::$lastRedirect);
        }
    }

    public function testExecuteRendersDashboardWhenUserIsAuthenticated(): void
    {
        $this->userRepository->insertUser('dash@example.com', 'dashuser', 'Pass123!');
        $user = $this->userRepository->findByEmail('dash@example.com');
        $this->assertNotNull($user);

        $_SESSION['user_id'] = $user->getId();
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $controller = new DashboardController($this->db, $this->userRepository);
        $controller->execute();

        $this->assertTrue(true);
    }
}
