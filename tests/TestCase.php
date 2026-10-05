<?php

namespace Tests;

use _assets\Includes\DatabaseConnection;
use PDO;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ob_start();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        \Controllers\DatabaseController::$isTesting = true;
        \Controllers\DatabaseController::$lastRedirect = null;
    }

    protected function tearDown(): void
    {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        parent::tearDown();
    }

    protected function createInMemoryDatabaseConnection(): DatabaseConnection
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec('
            CREATE TABLE Users (
                id_user INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT UNIQUE NOT NULL,
                username TEXT NOT NULL,
                password TEXT NOT NULL,
                reset_token TEXT DEFAULT NULL,
                reset_token_expiry TEXT DEFAULT NULL
            )
        ');

        return new DatabaseConnection($pdo);
    }
}
