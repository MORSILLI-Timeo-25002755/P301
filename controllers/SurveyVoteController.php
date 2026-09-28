<?php

namespace Controllers;

use _Assets\Includes\DatabaseConnection;
use models\AnswerRepository;
use models\FormRepository;
use models\QuestionRepository;
use models\UserRepository;
use Views\Error;
use Views\SurveyVote;

class SurveyVoteController
{
    public function execute(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $formId = (int)($_GET['id'] ?? $_POST['id_form'] ?? 0);

        if ($formId <= 0) {
            header('Location: /surveys');
            exit;
        }

        $db = new DatabaseConnection();
        $userRepo = new UserRepository($db);
        $formRepo = new FormRepository($db);
        $questionRepo = new QuestionRepository($db);
        $answerRepo = new AnswerRepository($db);

        $user = $userRepo->findById($userId);
        if ($user === null) {
            unset($_SESSION['user_id']);
            header('Location: /login');
            exit;
        }

        $form = $formRepo->getFormById($formId);
        if ($form === null) {
            http_response_code(404);
            (new Error('Sondage introuvable', "Le sondage demandé (ID #$formId) n'existe pas ou a été supprimé."))->show();
            return;
        }

        // RÈGLE : Seules les personnes qui NE SONT PAS propriétaires peuvent voter
        if ($form->getUserId() === $user->getId()) {
            (new SurveyVote(
                user: $user,
                form: $form,
                state: 'owner_forbidden'
            ))->show();
            return;
        }

        // Vérifier si l'utilisateur a déjà répondu à ce sondage
        if ($answerRepo->hasUserAnsweredForm($formId, $user->getId())) {
            (new SurveyVote(
                user: $user,
                form: $form,
                state: 'already_voted'
            ))->show();
            return;
        }

        $questions = $questionRepo->getQuestionsByFormId($formId);
        if (empty($questions)) {
            (new SurveyVote(
                user: $user,
                form: $form,
                state: 'no_questions'
            ))->show();
            return;
        }

        $error = null;

        // Traitement de la soumission du vote
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $submittedAnswers = $_POST['answers'] ?? [];

            // Validation de la présence de réponses à toutes les questions
            $missing = false;
            foreach ($questions as $q) {
                if (!isset($submittedAnswers[$q->getId()]) || trim((string)$submittedAnswers[$q->getId()]) === '') {
                    $missing = true;
                    break;
                }
            }

            if ($missing) {
                $error = 'Veuillez répondre à l\'ensemble des questions pour valider votre vote.';
            } else {
                $success = $answerRepo->saveAnswers($formId, $user->getId(), $submittedAnswers, $questions);
                if ($success) {
                    (new SurveyVote(
                        user: $user,
                        form: $form,
                        state: 'success'
                    ))->show();
                    return;
                } else {
                    $error = 'Une erreur est survenue lors de l\'enregistrement de votre vote. Veuillez réessayer.';
                }
            }
        }

        (new SurveyVote(
            user: $user,
            form: $form,
            questions: $questions,
            state: 'form',
            error: $error
        ))->show();
    }
}
