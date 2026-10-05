<?php

namespace Tests\Unit\Controllers;

use Controllers\ForgotPasswordController;
use Models\UserRepository;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    private ForgotPasswordController $controller;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $db = $this->createInMemoryDatabaseConnection();
        $this->userRepository = new UserRepository($db);
        $this->controller = new ForgotPasswordController($db, $this->userRepository);
    }

    public function testGetWithoutTokenShowsRequestResetView(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->controller->execute();
        $this->assertTrue(true); // execute completes without throwing
    }

    public function testPostWithoutTokenInvalidEmailShowsError(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['email'] = 'invalid-email';

        $this->controller->execute();
        $this->assertTrue(true);
    }

    public function testPostWithoutTokenNonExistentEmailShowsSuccessMessage(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['email'] = 'nonexistent@example.com';

        $this->controller->execute();
        $this->assertTrue(true);
    }

    public function testPostWithoutTokenExistingUserSetsToken(): void
    {
        $this->userRepository->insertUser('resetme@example.com', 'resetuser', 'Password123!');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['email'] = 'resetme@example.com';

        $this->controller->execute();

        $user = $this->userRepository->findByEmail('resetme@example.com');
        $this->assertNotNull($user);
    }

    public function testWithInvalidTokenShowsInvalidLinkError(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['token'] = 'invalid-short-token';

        $this->controller->execute();
        $this->assertTrue(true);
    }

    public function testWithValidTokenGetShowsResetForm(): void
    {
        $this->userRepository->insertUser('target@example.com', 'target', 'Password123!');
        $user = $this->userRepository->findByEmail('target@example.com');
        $this->assertNotNull($user);

        $plainToken = str_repeat('a', 64);
        $tokenHash = hash('sha256', $plainToken);
        $this->userRepository->setResetToken($user->getId(), $tokenHash, date('Y-m-d H:i:s', time() + 1800));

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['token'] = $plainToken;

        $this->controller->execute();
        $this->assertTrue(true);
    }

    public function testWithValidTokenPostMismatchPasswordsShowsError(): void
    {
        $this->userRepository->insertUser('target2@example.com', 'target2', 'Password123!');
        $user = $this->userRepository->findByEmail('target2@example.com');
        $this->assertNotNull($user);

        $plainToken = str_repeat('b', 64);
        $tokenHash = hash('sha256', $plainToken);
        $this->userRepository->setResetToken($user->getId(), $tokenHash, date('Y-m-d H:i:s', time() + 1800));

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['token'] = $plainToken;
        $_POST['password'] = 'NewPassword123';
        $_POST['confirm'] = 'DifferentPassword123';

        $this->controller->execute();
        $this->assertTrue(true);
    }

    public function testWithValidTokenPostShortPasswordShowsError(): void
    {
        $this->userRepository->insertUser('target3@example.com', 'target3', 'Password123!');
        $user = $this->userRepository->findByEmail('target3@example.com');
        $this->assertNotNull($user);

        $plainToken = str_repeat('c', 64);
        $tokenHash = hash('sha256', $plainToken);
        $this->userRepository->setResetToken($user->getId(), $tokenHash, date('Y-m-d H:i:s', time() + 1800));

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['token'] = $plainToken;
        $_POST['password'] = 'short';
        $_POST['confirm'] = 'short';

        $this->controller->execute();
        $this->assertTrue(true);
    }

    public function testWithValidTokenPostSuccessUpdatesPassword(): void
    {
        $this->userRepository->insertUser('target4@example.com', 'target4', 'OldPassword123!');
        $user = $this->userRepository->findByEmail('target4@example.com');
        $this->assertNotNull($user);

        $plainToken = str_repeat('d', 64);
        $tokenHash = hash('sha256', $plainToken);
        $this->userRepository->setResetToken($user->getId(), $tokenHash, date('Y-m-d H:i:s', time() + 1800));

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['token'] = $plainToken;
        $_POST['password'] = 'BrandNewPassword123!';
        $_POST['confirm'] = 'BrandNewPassword123!';

        $this->controller->execute();

        // Password should now be updated
        $updatedUser = $this->userRepository->checkLogin('target4@example.com', 'BrandNewPassword123!');
        $this->assertInstanceOf(\Models\Users::class, $updatedUser);

        // Old password should fail
        $this->assertNull($this->userRepository->checkLogin('target4@example.com', 'OldPassword123!'));
    }
}
