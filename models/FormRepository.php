<?php

namespace models;

use _Assets\Includes\DatabaseConnection;
use PDO;
use PDOStatement;

class FormRepository
{
    public function __construct(private DatabaseConnection $dbConnection) {}

    /**
     * @return Form[]
     */
    public function getFormsByUserId(int $userId): array
    {
        $statement = $this->run(
            'SELECT f.id_form, f.name, f.id_user,
                    COUNT(DISTINCT q.id_question) AS questions_count,
                    COUNT(DISTINCT a.id_answer) AS answers_count
             FROM Form f
             LEFT JOIN Question q ON q.id_form = f.id_form
             LEFT JOIN Answer a ON a.id_question = q.id_question
             WHERE f.id_user = :userId
             GROUP BY f.id_form, f.name, f.id_user
             ORDER BY f.id_form DESC',
            [':userId' => $userId]
        );

        $forms = [];
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $forms[] = new Form(
                (int)$row->id_form,
                $row->name,
                (int)$row->id_user,
                (int)$row->questions_count,
                (int)$row->answers_count
            );
        }

        return $forms;
    }

    public function createForm(string $name, int $userId): bool
    {
        try {
            $this->run(
                'INSERT INTO Form (name, id_user) VALUES (:name, :userId)',
                [
                    ':name' => $name,
                    ':userId' => $userId
                ]
            );
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function deleteForm(int $formId, int $userId): bool
    {
        try {
            $pdo = $this->dbConnection->getConnection();

            // Vérifier que le formulaire appartient bien à l'utilisateur
            $check = $pdo->prepare('SELECT id_form FROM Form WHERE id_form = :formId AND id_user = :userId');
            $check->execute([':formId' => $formId, ':userId' => $userId]);
            if ($check->fetch() === false) {
                return false;
            }

            $pdo->beginTransaction();

            // Récupérer les questions associées au formulaire
            $stmtQ = $pdo->prepare('SELECT id_question FROM Question WHERE id_form = :formId');
            $stmtQ->execute([':formId' => $formId]);
            $questionIds = $stmtQ->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($questionIds)) {
                $inQuestions = implode(',', array_map('intval', $questionIds));

                // Récupérer les réponses associées
                $stmtA = $pdo->query("SELECT id_answer FROM Answer WHERE id_question IN ($inQuestions)");
                $answerIds = $stmtA->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($answerIds)) {
                    $inAnswers = implode(',', array_map('intval', $answerIds));
                    $pdo->exec("DELETE FROM Ans_FreeText WHERE id_answer IN ($inAnswers)");
                    $pdo->exec("DELETE FROM Ans_Grade WHERE id_answer IN ($inAnswers)");
                    $pdo->exec("DELETE FROM Ans_Selection WHERE id_answer IN ($inAnswers)");
                    $pdo->exec("DELETE FROM Answer WHERE id_question IN ($inQuestions)");
                }

                $pdo->exec("DELETE FROM Choice WHERE id_question IN ($inQuestions)");
                $pdo->exec("DELETE FROM Selection WHERE id_question IN ($inQuestions)");
                $pdo->exec("DELETE FROM FreeText WHERE id_question IN ($inQuestions)");
                $pdo->exec("DELETE FROM Grade WHERE id_question IN ($inQuestions)");
                $pdo->exec("DELETE FROM Question WHERE id_form = " . (int)$formId);
            }

            $delForm = $pdo->prepare('DELETE FROM Form WHERE id_form = :formId AND id_user = :userId');
            $delForm->execute([':formId' => $formId, ':userId' => $userId]);

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }

    public function countTotalAnswersByUserId(int $userId): int
    {
        $statement = $this->run(
            'SELECT COUNT(DISTINCT a.id_answer) AS total
             FROM Answer a
             JOIN Question q ON a.id_question = q.id_question
             JOIN Form f ON q.id_form = f.id_form
             WHERE f.id_user = :userId',
            [':userId' => $userId]
        );

        $row = $statement->fetch(PDO::FETCH_OBJ);
        return $row ? (int)$row->total : 0;
    }

    private function run(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->dbConnection->getConnection()->prepare($sql);

        if ($statement === false || !$statement->execute($params)) {
            throw new \PDOException('Query failed: ' . $sql);
        }

        return $statement;
    }
}
