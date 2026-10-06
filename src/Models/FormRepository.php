<?php

namespace Models;

use \_assets\Includes\DatabaseConnection;
use http\Exception\RuntimeException;
use PDO;
use PDOStatement;

class FormRepository
{
    public function __construct(private DatabaseConnection $dbConnection) {}

    public function findNumberOfFormPerUser(int $userId): int
    {
        $statement = $this->run(
            'SELECT COUNT(id_form) FROM Form WHERE id_user = :idUser', [':idUser' => $userId]
        );
        return (int) $statement->fetchColumn();
    }

    public function findFormInformations(int $userId, int $limit, int $offset): array
    {
        $statement = $this->run(
            'SELECT f.id_form, f.name, (SELECT COUNT(q.id_question) FROM Question q WHERE q.id_form = f.id_form) AS nb_questions, (SELECT COUNT(DISTINCT a.id_user) FROM Answer a INNER JOIN Question q ON a.id_question = q.id_question WHERE q.id_form = f.id_form) AS nb_answers FROM Form f WHERE f.id_user = :idUser LIMIT :limit OFFSET :offset', [':idUser' => $userId, ':limit' => $limit, ':offset' => $offset]
        );
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function run(string $sql, array $params): PDOStatement
    {
        $statement = $this->dbConnection->getConnection()->prepare($sql);

        foreach ($params as $param => $value) {
            $statement->bindValue($param, $value, PDO::PARAM_INT);
        }

        if ($statement === false || !$statement->execute()) {
            throw new RuntimeException('Wrong query');
        }

        return $statement;
    }
}