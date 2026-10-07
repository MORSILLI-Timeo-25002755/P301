<?php

namespace Models;

use \_assets\Includes\DatabaseConnection;
use http\Exception\RuntimeException;
use PDO;
use PDOStatement;

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
            'SELECT id_user, email, username FROM Users WHERE email = :email',
            [':email' => $email]
        );
        $row = $statement->fetch(PDO::FETCH_OBJ);

        if (!$row) {
            return null;
        }

        return new Users($row->id_user, $row->email, $row->username);
    }

    public function findById(int $id): ?Users
    {
        $statement = $this->run(
            'SELECT id_user, email, username FROM Users WHERE id_user = :id_user',
            [':id_user' => $id]
        );
        $row = $statement->fetch(PDO::FETCH_OBJ);

        if (!$row) {
            return null;
        }

        return new Users($row->id_user, $row->email, $row->username);
    }

    public function findByUsername(string $username): ?Users
    {
        $statement = $this->run(
            'SELECT id_user, email, username FROM Users WHERE username = :username',
            [':username' => $username]
        );
        $row = $statement->fetch(PDO::FETCH_OBJ);

        if (!$row) {
            return null;
        }

        return new Users($row->id_user, $row->email, $row->username);
    }

    public function findByResetToken(string $tokenHash): ?Users
    {
        $statement = $this->run(
            'SELECT id_user, email, username, reset_token_expiry FROM Users WHERE reset_token = :tokenHash',
            [':tokenHash' => $tokenHash]
        );
        $row = $statement->fetch(PDO::FETCH_OBJ);

        if ($row === false) {
            return null;
        }

        return new Users($row->id_user, $row->email, $row->username, $row->reset_token_expiry);
    }
    public function checkLogin($email, $password): ?Users
    {
        $statement = $this->run(
            'SELECT id_user, email, username, password FROM Users WHERE email = :email',
            [':email' => $email]
        );

        $row = $statement->fetch(PDO::FETCH_OBJ);

        if ($row === false || !password_verify($password, $row->password)) {
            return null;
        }

        return new Users($row->id_user, $row->email, $row->username);
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

    public function updateProfile(int $id, string $email, string $username, ?string $password = null): bool
    {
        try {
            if ($password === null) {
                $this->run(
                    'UPDATE Users SET email = :email, username = :username WHERE id_user = :id',
                    [':email' => $email, ':username' => $username, ':id' => $id]
                );
            } else {
                $this->run(
                    'UPDATE Users SET email = :email, username = :username, password = :password WHERE id_user = :id',
                    [
                        ':email' => $email,
                        ':username' => $username,
                        ':password' => password_hash($password, PASSWORD_DEFAULT),
                        ':id' => $id
                    ]
                );
            }

            return true;
        } catch (\PDOException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) {
                return false;
            }

            throw $e;
        }
    }

    public function deleteUser(int $userId): void
    {
        $pdo = $this->dbConnection->getConnection();
        $pdo->beginTransaction();

        try {
            $formIds = $this->fetchIds('SELECT id_form FROM Form WHERE id_user = :userId', [':userId' => $userId]);
            $questionIds = $formIds === []
                ? []
                : $this->fetchIds(
                    'SELECT id_question FROM Question WHERE id_form IN (' . $this->placeholders(count($formIds)) . ')',
                    $this->indexedParams($formIds)
                );

            $answerIds = $this->fetchIds(
                'SELECT id_answer FROM Answer WHERE id_user = ?'
                . ($questionIds === [] ? '' : ' OR id_question IN (' . $this->placeholders(count($questionIds)) . ')'),
                array_merge([$userId], $this->indexedParams($questionIds))
            );

            if ($answerIds !== []) {
                $params = $this->indexedParams($answerIds);
                $in = $this->placeholders(count($answerIds));
                $this->run('DELETE FROM Ans_Selection WHERE id_answer IN (' . $in . ')', $params);
                $this->run('DELETE FROM Ans_Grade WHERE id_answer IN (' . $in . ')', $params);
                $this->run('DELETE FROM Ans_FreeText WHERE id_answer IN (' . $in . ')', $params);
                $this->run('DELETE FROM Answer WHERE id_answer IN (' . $in . ')', $params);
            }

            if ($questionIds !== []) {
                $params = $this->indexedParams($questionIds);
                $in = $this->placeholders(count($questionIds));
                $this->run('DELETE FROM Choice WHERE id_question IN (' . $in . ')', $params);
                $this->run('DELETE FROM Selection WHERE id_question IN (' . $in . ')', $params);
                $this->run('DELETE FROM Grade WHERE id_question IN (' . $in . ')', $params);
                $this->run('DELETE FROM FreeText WHERE id_question IN (' . $in . ')', $params);
                $this->run('DELETE FROM Question WHERE id_question IN (' . $in . ')', $params);
            }

            if ($formIds !== []) {
                $this->run(
                    'DELETE FROM Form WHERE id_form IN (' . $this->placeholders(count($formIds)) . ')',
                    $this->indexedParams($formIds)
                );
            }

            $this->run('DELETE FROM Users WHERE id_user = :userId', [':userId' => $userId]);
            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function fetchIds(string $sql, array $params): array
    {
        return array_map('intval', $this->run($sql, $params)->fetchAll(PDO::FETCH_COLUMN));
    }

    private function placeholders(int $count): string
    {
        return implode(', ', array_fill(0, $count, '?'));
    }

    private function indexedParams(array $values): array
    {
        return array_values($values);
    }

    private function run(string $sql, array $params): PDOStatement
    {
        $statement = $this->dbConnection->getConnection()->prepare($sql);

        if ($statement === false || !$statement->execute($params)) {
            throw new RuntimeException('Wrong query');
        }

        return $statement;
    }
}