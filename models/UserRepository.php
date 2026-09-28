<?php

namespace models;

use models\Users;
use PDO;
use PDOStatement;
use _Assets\Includes\DatabaseConnection;

class UserRepository
{
    public function __construct(private DatabaseConnection $dbConnection) {}

    public function insertUser(string $email, string $username, string $password): bool
    {
        try {
            $this->run(
                'INSERT INTO Users (email, username, password)
                 VALUES (:email, :username, :password)',
                [
                    ':email' => $email,
                    ':username' => $username,
                    ':password' => password_hash($password, PASSWORD_DEFAULT)
                ]
            );

            return true;
        } catch (\PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                // Email ou username déjà utilisé
                return false;
            }
            return false;
        }
    }

    public function findByEmail(string $email): ?Users
    {
        $statement = $this->run(
            'SELECT id_user, email FROM Users WHERE email = :email',
            [':email' => $email]
        );
        $row = $statement->fetch(PDO::FETCH_OBJ);

        return new Users($row->id_user, $row->email);
    }

    public function findByResetToken(string $tokenHash): ?Users
    {
        $statement = $this->run(
            'SELECT id_user, email, reset_token_expiry FROM Users WHERE reset_token = :tokenHash',
            [':tokenHash' => $tokenHash]
        );
        $row = $statement->fetch(PDO::FETCH_OBJ);

        if ($row === false) {
            return null;
        }

        return new Users($row->id_user, $row->email, $row->reset_token_expiry);
    }
    public function checkLogin($email, $password): ?Users
    {
        $statement = $this->run(
            'SELECT id_user, email, password FROM Users WHERE email = :email',
            [':email' => $email]
        );

        $row = $statement->fetch(PDO::FETCH_OBJ);

        if ($row === false || !password_verify($password, $row->password)) {
            return null;
        }

        return new Users($row->id_user, $row->email);
    }

    public function setResetToken(int $id, string $tokenHash, string $expiry): void
    {
        $this->run(
            'UPDATE Users SET reset_token = :token, reset_token_expiry = :expiry WHERE id_user = :id',
            [':token' => $tokenHash, ':expiry' => $expiry, ':id' => $id]
        );
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $this->run(
            'UPDATE Users SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE id_user = :id',
            [':password' => $passwordHash, ':id' => $id]
        );
    }

    private function run(string $sql, array $params): PDOStatement
    {
        $statement = $this->dbConnection->getConnection()->prepare($sql);

        if ($statement === false || !$statement->execute($params)) {
            throw new DatabaseException('Wrong query');
        }

        return $statement;
    }
}