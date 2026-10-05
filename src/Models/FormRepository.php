<?php

namespace Models;

use \_assets\Includes\DatabaseConnection;
use \Models\DatabaseException;
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

    public function findFormInformations(int $userId): array
    {
        $statement = $this->run(
            'SELECT f.id_form, f.name, (SELECT COUNT(q.id_question) FROM Question q WHERE q.id_form = f.id_form) AS nb_questions, (SELECT COUNT(DISTINCT a.id_user) FROM Answer a INNER JOIN Question q ON a.id_question = q.id_question WHERE q.id_form = f.id_form) AS nb_answers FROM Form f WHERE f.id_user = :idUser', [':idUser' => $userId]
        );
        return $statement->fetchAll(PDO::FETCH_ASSOC);
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