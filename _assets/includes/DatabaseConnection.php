<?php

namespace _Assets\Includes;

require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use PDO;
use PDOException;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();
class DatabaseConnection {
    private string $host;
    private string $user;
    private string $password;
    private string $dbname;
    private PDO $pdo;
    public function __construct() {
        $this->host = $_ENV['DB_HOST'];
        $this->user = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASSWORD'];
        $this->dbname = $_ENV['DB_DBNAME'];
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname}";
            $this->pdo = new PDO($dsn, $this->user, $this->password);
            $this->pdo->exec("set character set utf8");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            (new \Views\Error('Connection failed: ' . $e->getMessage()))->show();
        }
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }
}