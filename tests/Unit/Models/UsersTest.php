<?php

namespace Tests\Unit\Models;

use Models\Users;
use Tests\TestCase;

class UsersTest extends TestCase
{
    public function testGettersReturnCorrectValues(): void
    {
        $user = new Users(1, 'alice@example.com', 'alice', '2030-01-01 00:00:00');

        $this->assertSame(1, $user->getId());
        $this->assertSame('alice@example.com', $user->getEmail());
        $this->assertSame('alice', $user->getUsername());
    }

    public function testGetUsernameCanBeNull(): void
    {
        $user = new Users(2, 'bob@example.com', null);

        $this->assertSame(2, $user->getId());
        $this->assertSame('bob@example.com', $user->getEmail());
        $this->assertNull($user->getUsername());
    }

    public function testHasValidResetTokenReturnsFalseWhenExpiryIsNull(): void
    {
        $user = new Users(1, 'alice@example.com', 'alice', null);

        $this->assertFalse($user->hasValidResetToken());
    }

    public function testHasValidResetTokenReturnsFalseWhenExpiryIsInThePast(): void
    {
        $pastDate = date('Y-m-d H:i:s', time() - 3600);
        $user = new Users(1, 'alice@example.com', 'alice', $pastDate);

        $this->assertFalse($user->hasValidResetToken());
    }

    public function testHasValidResetTokenReturnsTrueWhenExpiryIsInTheFuture(): void
    {
        $futureDate = date('Y-m-d H:i:s', time() + 3600);
        $user = new Users(1, 'alice@example.com', 'alice', $futureDate);

        $this->assertTrue($user->hasValidResetToken());
    }
}
