<?php

namespace models;

use _Assets\Includes\DatabaseConnection;
use PDO;
use PDOStatement;

class QuestionRepository
{
    public function __construct(private DatabaseConnection $dbConnection) {}

    /**
     * @return Question[]
     */
    public function getQuestionsByFormId(int $formId): array
    {
        $statement = $this->run(
            "SELECT q.id_question, q.title, q.order_, q.id_form,
                    CASE
                        WHEN s.id_question IS NOT NULL THEN 'selection'
                        WHEN g.id_question IS NOT NULL THEN 'grade'
                        WHEN ft.id_question IS NOT NULL THEN 'freetext'
                        ELSE 'freetext'
                    END AS question_type
             FROM Question q
             LEFT JOIN Selection s ON s.id_question = q.id_question
             LEFT JOIN Grade g ON g.id_question = q.id_question
             LEFT JOIN FreeText ft ON ft.id_question = q.id_question
             WHERE q.id_form = :formId
             ORDER BY q.order_ ASC, q.id_question ASC",
            [':formId' => $formId]
        );

        $rows = $statement->fetchAll(PDO::FETCH_OBJ);
        if (empty($rows)) {
            return [];
        }

        // Collect selection question IDs to fetch choices in one batch
        $selectionQuestionIds = [];
        foreach ($rows as $row) {
            if ($row->question_type === Question::TYPE_SELECTION) {
                $selectionQuestionIds[] = (int)$row->id_question;
            }
        }

        $choicesByQuestion = [];
        if (!empty($selectionQuestionIds)) {
            $inClause = implode(',', $selectionQuestionIds);
            $choiceStmt = $this->run(
                "SELECT id_choice, title, id_question
                 FROM Choice
                 WHERE id_question IN ($inClause)
                 ORDER BY id_choice ASC"
            );
            while ($cRow = $choiceStmt->fetch(PDO::FETCH_OBJ)) {
                $qId = (int)$cRow->id_question;
                if (!isset($choicesByQuestion[$qId])) {
                    $choicesByQuestion[$qId] = [];
                }
                $choicesByQuestion[$qId][] = new Choice(
                    (int)$cRow->id_choice,
                    $cRow->title,
                    $qId
                );
            }
        }

        $questions = [];
        foreach ($rows as $row) {
            $qId = (int)$row->id_question;
            $type = $row->question_type;
            $choices = $choicesByQuestion[$qId] ?? [];

            $questions[] = new Question(
                $qId,
                $row->title,
                (int)$row->order_,
                (int)$row->id_form,
                $type,
                $choices
            );
        }

        return $questions;
    }

    /**
     * @param string[] $choices
     */
    public function createQuestion(int $formId, string $title, string $type, int $order, array $choices = []): int|false
    {
        $pdo = $this->dbConnection->getConnection();
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('INSERT INTO Question (title, order_, id_form) VALUES (:title, :order, :formId)');
            $stmt->execute([
                ':title'  => mb_substr($title, 0, 50),
                ':order'  => $order,
                ':formId' => $formId
            ]);
            $questionId = (int)$pdo->lastInsertId();

            if ($type === Question::TYPE_SELECTION) {
                $pdo->prepare('INSERT INTO Selection (id_question) VALUES (:questionId)')
                    ->execute([':questionId' => $questionId]);

                $choiceStmt = $pdo->prepare('INSERT INTO Choice (title, id_question) VALUES (:title, :questionId)');
                foreach ($choices as $choiceTitle) {
                    $cleanChoice = trim((string)$choiceTitle);
                    if ($cleanChoice !== '') {
                        $choiceStmt->execute([
                            ':title'      => mb_substr($cleanChoice, 0, 50),
                            ':questionId' => $questionId
                        ]);
                    }
                }
            } elseif ($type === Question::TYPE_GRADE) {
                $pdo->prepare('INSERT INTO Grade (id_question) VALUES (:questionId)')
                    ->execute([':questionId' => $questionId]);
            } elseif ($type === Question::TYPE_FREETEXT) {
                $pdo->prepare('INSERT INTO FreeText (id_question) VALUES (:questionId)')
                    ->execute([':questionId' => $questionId]);
            } else {
                throw new \InvalidArgumentException('Type de question inconnu : ' . $type);
            }

            $pdo->commit();
            return $questionId;
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }

    public function deleteQuestion(int $questionId, int $userId): bool
    {
        $pdo = $this->dbConnection->getConnection();
        try {
            // Vérifier les droits du créateur sur la question
            $check = $pdo->prepare(
                'SELECT q.id_question
                 FROM Question q
                 JOIN Form f ON f.id_form = q.id_form
                 WHERE q.id_question = :questionId AND f.id_user = :userId'
            );
            $check->execute([':questionId' => $questionId, ':userId' => $userId]);
            if ($check->fetch() === false) {
                return false;
            }

            $pdo->beginTransaction();

            // Supprimer les réponses liées
            $stmtA = $pdo->prepare('SELECT id_answer FROM Answer WHERE id_question = :questionId');
            $stmtA->execute([':questionId' => $questionId]);
            $answerIds = $stmtA->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($answerIds)) {
                $inAnswers = implode(',', array_map('intval', $answerIds));
                $pdo->exec("DELETE FROM Ans_FreeText WHERE id_answer IN ($inAnswers)");
                $pdo->exec("DELETE FROM Ans_Grade WHERE id_answer IN ($inAnswers)");
                $pdo->exec("DELETE FROM Ans_Selection WHERE id_answer IN ($inAnswers)");
                $pdo->exec("DELETE FROM Answer WHERE id_question = " . (int)$questionId);
            }

            // Supprimer les choix et sous-types
            $pdo->prepare('DELETE FROM Choice WHERE id_question = :id')->execute([':id' => $questionId]);
            $pdo->prepare('DELETE FROM Selection WHERE id_question = :id')->execute([':id' => $questionId]);
            $pdo->prepare('DELETE FROM Grade WHERE id_question = :id')->execute([':id' => $questionId]);
            $pdo->prepare('DELETE FROM FreeText WHERE id_question = :id')->execute([':id' => $questionId]);
            $pdo->prepare('DELETE FROM Question WHERE id_question = :id')->execute([':id' => $questionId]);

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }

    public function getNextOrder(int $formId): int
    {
        $statement = $this->run(
            'SELECT COALESCE(MAX(order_), 0) + 1 AS next_order FROM Question WHERE id_form = :formId',
            [':formId' => $formId]
        );
        $row = $statement->fetch(PDO::FETCH_OBJ);
        return $row ? (int)$row->next_order : 1;
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
