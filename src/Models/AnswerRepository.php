<?php

namespace Models;

use \_assets\Includes\DatabaseConnection;
use \Models\DatabaseException;
use PDO;
use PDOStatement;

class AnswerRepository
{
    public function __construct(private DatabaseConnection $dbConnection) {}

    public function findNumberOfAnswerPerUser(int $userId): int
    {
        $statement = $this->run(
            'SELECT COUNT(DISTINCT Answer.id_user) FROM Answer INNER JOIN Question ON Answer.id_question = Question.id_question INNER JOIN Form ON Question.id_form = Form.id_user WHERE Form.id_user = :idUser', [':idUser' => $userId]
        );
        return (int) $statement->fetchColumn();
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