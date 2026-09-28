<?php

namespace models;

use _Assets\Includes\DatabaseConnection;
use PDO;
use PDOStatement;

class AnswerRepository
{
    public function __construct(private DatabaseConnection $dbConnection) {}

    public function hasUserAnsweredForm(int $formId, int $userId): bool
    {
        $statement = $this->run(
            'SELECT 1
             FROM Answer a
             JOIN Question q ON a.id_question = q.id_question
             WHERE q.id_form = :formId AND a.id_user = :userId
             LIMIT 1',
            [':formId' => $formId, ':userId' => $userId]
        );

        return $statement->fetch() !== false;
    }

    /**
     * @param Question[] $questions
     * @param array<int, mixed> $submittedAnswers [question_id => answer_value]
     */
    public function saveAnswers(int $formId, int $userId, array $submittedAnswers, array $questions): bool
    {
        $pdo = $this->dbConnection->getConnection();

        try {
            $pdo->beginTransaction();

            // Double vérification : l'utilisateur ne doit pas déjà avoir répondu
            $checkStmt = $pdo->prepare(
                'SELECT 1
                 FROM Answer a
                 JOIN Question q ON a.id_question = q.id_question
                 WHERE q.id_form = :formId AND a.id_user = :userId
                 LIMIT 1'
            );
            $checkStmt->execute([':formId' => $formId, ':userId' => $userId]);
            if ($checkStmt->fetch() !== false) {
                $pdo->rollBack();
                return false;
            }

            $ansStmt = $pdo->prepare('INSERT INTO Answer (id_user, id_question) VALUES (:userId, :questionId)');
            $selStmt = $pdo->prepare('INSERT INTO Ans_Selection (id_answer, id_choice) VALUES (:answerId, :choiceId)');
            $grdStmt = $pdo->prepare('INSERT INTO Ans_Grade (id_answer, score) VALUES (:answerId, :score)');
            $txtStmt = $pdo->prepare('INSERT INTO Ans_FreeText (id_answer, text) VALUES (:answerId, :text)');

            foreach ($questions as $question) {
                $qId = $question->getId();
                if (!isset($submittedAnswers[$qId])) {
                    continue;
                }

                $value = $submittedAnswers[$qId];

                // Insertion dans Answer
                $ansStmt->execute([
                    ':userId'     => $userId,
                    ':questionId' => $qId
                ]);
                $answerId = (int)$pdo->lastInsertId();

                if ($question->isSelection()) {
                    $choiceId = (int)$value;
                    // Vérifier que le choix appartient bien à cette question
                    $validChoice = false;
                    foreach ($question->getChoices() as $choice) {
                        if ($choice->getId() === $choiceId) {
                            $validChoice = true;
                            break;
                        }
                    }

                    if ($validChoice) {
                        $selStmt->execute([
                            ':answerId' => $answerId,
                            ':choiceId' => $choiceId
                        ]);
                    }
                } elseif ($question->isGrade()) {
                    $score = max(1, min(5, (int)$value));
                    $grdStmt->execute([
                        ':answerId' => $answerId,
                        ':score'    => $score
                    ]);
                } elseif ($question->isFreeText()) {
                    $text = trim((string)$value);
                    if ($text !== '') {
                        $txtStmt->execute([
                            ':answerId' => $answerId,
                            ':text'     => $text
                        ]);
                    }
                }
            }

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }

    /**
     * @return array{
     *     total_respondents: int,
     *     total_answers: int,
     *     questions: array
     * }
     */
    public function getSurveyStats(int $formId): array
    {
        $pdo = $this->dbConnection->getConnection();

        // 1. Nombre total de répondants uniques
        $stmtResp = $pdo->prepare(
            'SELECT COUNT(DISTINCT a.id_user) AS total
             FROM Answer a
             JOIN Question q ON a.id_question = q.id_question
             WHERE q.id_form = :formId'
        );
        $stmtResp->execute([':formId' => $formId]);
        $totalRespondents = (int)$stmtResp->fetchColumn();

        // 2. Nombre total de réponses enregistrées
        $stmtAns = $pdo->prepare(
            'SELECT COUNT(a.id_answer) AS total
             FROM Answer a
             JOIN Question q ON a.id_question = q.id_question
             WHERE q.id_form = :formId'
        );
        $stmtAns->execute([':formId' => $formId]);
        $totalAnswers = (int)$stmtAns->fetchColumn();

        // 3. Récupérer toutes les questions du sondage
        $questionRepo = new QuestionRepository($this->dbConnection);
        $questions = $questionRepo->getQuestionsByFormId($formId);

        $questionsStats = [];
        foreach ($questions as $question) {
            $qId = $question->getId();
            $qStat = [
                'question'        => $question,
                'total_responses' => 0,
                'data'            => []
            ];

            if ($question->isSelection()) {
                // Total des réponses à cette question
                $stmtQTotal = $pdo->prepare(
                    'SELECT COUNT(ans.id_answer)
                     FROM Answer a
                     JOIN Ans_Selection ans ON a.id_answer = ans.id_answer
                     WHERE a.id_question = :questionId'
                );
                $stmtQTotal->execute([':questionId' => $qId]);
                $qTotal = (int)$stmtQTotal->fetchColumn();
                $qStat['total_responses'] = $qTotal;

                // Votes par choix
                $stmtChoices = $pdo->prepare(
                    'SELECT c.id_choice, c.title, COUNT(ans.id_answer) AS votes
                     FROM Choice c
                     LEFT JOIN Ans_Selection ans ON c.id_choice = ans.id_choice
                     WHERE c.id_question = :questionId
                     GROUP BY c.id_choice, c.title
                     ORDER BY c.id_choice ASC'
                );
                $stmtChoices->execute([':questionId' => $qId]);
                $choiceRows = $stmtChoices->fetchAll(PDO::FETCH_OBJ);

                $choicesData = [];
                $maxVotes = 0;
                foreach ($choiceRows as $cRow) {
                    $votes = (int)$cRow->votes;
                    if ($votes > $maxVotes) {
                        $maxVotes = $votes;
                    }
                    $percentage = $qTotal > 0 ? round(($votes / $qTotal) * 100, 1) : 0;
                    $choicesData[] = [
                        'id_choice'  => (int)$cRow->id_choice,
                        'title'      => $cRow->title,
                        'votes'      => $votes,
                        'percentage' => $percentage
                    ];
                }

                $qStat['data'] = [
                    'choices'   => $choicesData,
                    'max_votes' => $maxVotes
                ];

            } elseif ($question->isGrade()) {
                // Statistiques de notation
                $stmtGrade = $pdo->prepare(
                    'SELECT COUNT(ans.id_answer) AS count,
                            AVG(ans.score) AS avg_score,
                            MIN(ans.score) AS min_score,
                            MAX(ans.score) AS max_score
                     FROM Answer a
                     JOIN Ans_Grade ans ON a.id_answer = ans.id_answer
                     WHERE a.id_question = :questionId'
                );
                $stmtGrade->execute([':questionId' => $qId]);
                $gradeSummary = $stmtGrade->fetch(PDO::FETCH_OBJ);

                $qTotal = $gradeSummary ? (int)$gradeSummary->count : 0;
                $qStat['total_responses'] = $qTotal;

                // Distribution des notes 1 à 5
                $stmtDist = $pdo->prepare(
                    'SELECT ans.score, COUNT(ans.id_answer) AS count
                     FROM Answer a
                     JOIN Ans_Grade ans ON a.id_answer = ans.id_answer
                     WHERE a.id_question = :questionId
                     GROUP BY ans.score'
                );
                $stmtDist->execute([':questionId' => $qId]);
                $distRows = $stmtDist->fetchAll(PDO::FETCH_KEY_PAIR);

                $distribution = [];
                for ($s = 1; $s <= 5; $s++) {
                    $count = isset($distRows[$s]) ? (int)$distRows[$s] : 0;
                    $pct = $qTotal > 0 ? round(($count / $qTotal) * 100, 1) : 0;
                    $distribution[$s] = [
                        'count'      => $count,
                        'percentage' => $pct
                    ];
                }

                $qStat['data'] = [
                    'average'      => $gradeSummary && $gradeSummary->avg_score !== null ? round((float)$gradeSummary->avg_score, 1) : null,
                    'min'          => $gradeSummary && $gradeSummary->min_score !== null ? (int)$gradeSummary->min_score : null,
                    'max'          => $gradeSummary && $gradeSummary->max_score !== null ? (int)$gradeSummary->max_score : null,
                    'distribution' => $distribution
                ];

            } elseif ($question->isFreeText()) {
                // Réponses en texte libre
                $stmtTxt = $pdo->prepare(
                    'SELECT ans.text, u.username, u.email
                     FROM Answer a
                     JOIN Ans_FreeText ans ON a.id_answer = ans.id_answer
                     JOIN Users u ON a.id_user = u.id_user
                     WHERE a.id_question = :questionId
                     ORDER BY a.id_answer DESC'
                );
                $stmtTxt->execute([':questionId' => $qId]);
                $txtRows = $stmtTxt->fetchAll(PDO::FETCH_OBJ);

                $qStat['total_responses'] = count($txtRows);
                $responses = [];
                foreach ($txtRows as $tRow) {
                    $responses[] = [
                        'text'     => $tRow->text,
                        'username' => $tRow->username ?: $tRow->email
                    ];
                }

                $qStat['data'] = [
                    'responses' => $responses
                ];
            }

            $questionsStats[] = $qStat;
        }

        return [
            'total_respondents' => $totalRespondents,
            'total_answers'     => $totalAnswers,
            'questions'         => $questionsStats
        ];
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
