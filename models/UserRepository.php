<?php

namespace models;

use models\Users;
use PDO;
use PDOStatement;
use _Assets\Includes\DatabaseConnection;

class UserRepository
{
    public function __construct(private DatabaseConnection $dbConnection) {}

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

        return new Users($row->id_user, $row->email, $row->password);
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