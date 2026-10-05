<?php

namespace Tests\Unit\Includes;

use _assets\Includes\DatabaseConnection;
use PDO;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    public function testGetConnectionReturnsInjectedPdo(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $db = new DatabaseConnection($pdo);

        $this->assertSame($pdo, $db->getConnection());
    }
}
