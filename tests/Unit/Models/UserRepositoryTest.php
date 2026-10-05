<?php

namespace Tests\Unit\Models;

use Models\DatabaseException;
use Models\UserRepository;
use Models\Users;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $db = $this->createInMemoryDatabaseConnection();
        $this->repository = new UserRepository($db);
    }

    public function testInsertUserSuccess(): void
    {
        $result = $this->repository->insertUser('john@example.com', 'john_doe', 'password123');

        $this->assertTrue($result);

        $user = $this->repository->findByEmail('john@example.com');
        $this->assertInstanceOf(Users::class, $user);
        $this->assertSame('john@example.com', $user->getEmail());
        $this->assertSame('john_doe', $user->getUsername());
    }

    public function testInsertUserFailsOnDuplicateEmail(): void
    {
        $this->repository->insertUser('john@example.com', 'john_doe', 'password123');
        $result = $this->repository->insertUser('john@example.com', 'john_other', 'password456');

        $this->assertFalse($result);
    }

    public function testFindByEmailReturnsNullWhenNotFound(): void
    {
        $user = $this->repository->findByEmail('unknown@example.com');

        $this->assertNull($user);
    }

    public function testFindByIdReturnsUserWhenFound(): void
    {
        $this->repository->insertUser('user1@example.com', 'user1', 'pass123');
        $created = $this->repository->findByEmail('user1@example.com');
        $this->assertNotNull($created);

        $user = $this->repository->findById($created->getId());
        $this->assertInstanceOf(Users::class, $user);
        $this->assertSame($created->getId(), $user->getId());
        $this->assertSame('user1@example.com', $user->getEmail());
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $user = $this->repository->findById(9999);

        $this->assertNull($user);
    }

    public function testFindByResetTokenReturnsUserWhenFound(): void
    {
        $this->repository->insertUser('user2@example.com', 'user2', 'pass123');
        $user = $this->repository->findByEmail('user2@example.com');
        $this->assertNotNull($user);

        $tokenHash = hash('sha256', 'reset-token-secret');
        $expiry = date('Y-m-d H:i:s', time() + 3600);
        $this->repository->setResetToken($user->getId(), $tokenHash, $expiry);

        $found = $this->repository->findByResetToken($tokenHash);
        $this->assertInstanceOf(Users::class, $found);
        $this->assertSame($user->getId(), $found->getId());
        $this->assertTrue($found->hasValidResetToken());
    }

    public function testFindByResetTokenReturnsNullWhenNotFound(): void
    {
        $user = $this->repository->findByResetToken('non-existent-token-hash');

        $this->assertNull($user);
    }

    public function testCheckLoginWithCorrectCredentials(): void
    {
        $this->repository->insertUser('user3@example.com', 'user3', 'SecretPassword!');

        $user = $this->repository->checkLogin('user3@example.com', 'SecretPassword!');
        $this->assertInstanceOf(Users::class, $user);
        $this->assertSame('user3@example.com', $user->getEmail());
    }

    public function testCheckLoginWithIncorrectPassword(): void
    {
        $this->repository->insertUser('user4@example.com', 'user4', 'SecretPassword!');

        $user = $this->repository->checkLogin('user4@example.com', 'WrongPassword');
        $this->assertNull($user);
    }

    public function testCheckLoginWithNonExistentEmail(): void
    {
        $user = $this->repository->checkLogin('doesnotexist@example.com', 'SomePassword');
        $this->assertNull($user);
    }

    public function testUpdatePasswordChangesPasswordAndClearsResetToken(): void
    {
        $this->repository->insertUser('user5@example.com', 'user5', 'OldPassword!');
        $user = $this->repository->findByEmail('user5@example.com');
        $this->assertNotNull($user);

        $tokenHash = hash('sha256', 'token-to-be-cleared');
        $this->repository->setResetToken($user->getId(), $tokenHash, date('Y-m-d H:i:s', time() + 3600));

        $newPasswordHash = password_hash('NewPassword123!', PASSWORD_DEFAULT);
        $this->repository->updatePassword($user->getId(), $newPasswordHash);

        // Check that old password fails and new password works
        $this->assertNull($this->repository->checkLogin('user5@example.com', 'OldPassword!'));
        $loggedIn = $this->repository->checkLogin('user5@example.com', 'NewPassword123!');
        $this->assertInstanceOf(Users::class, $loggedIn);

        // Reset token must be cleared
        $this->assertNull($this->repository->findByResetToken($tokenHash));
    }

    public function testQueryFailureThrowsDatabaseException(): void
    {
        $pdo = $this->createStub(\PDO::class);
        $statement = $this->createStub(\PDOStatement::class);
        $statement->method('execute')->willReturn(false);
        $pdo->method('prepare')->willReturn($statement);

        $db = new \_assets\Includes\DatabaseConnection($pdo);
        $repo = new UserRepository($db);

        $this->expectException(DatabaseException::class);
        $repo->findByEmail('test@example.com');
    }
}
