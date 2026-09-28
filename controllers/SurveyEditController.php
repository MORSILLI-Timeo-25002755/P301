<?php

namespace Controllers;

use _Assets\Includes\DatabaseConnection;
use models\FormRepository;
use models\Question;
use models\QuestionRepository;
use models\UserRepository;
use Views\Error;
use Views\SurveyEdit;

class SurveyEditController
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
            header('Location: /panel');
            exit;
        }

        $db = new DatabaseConnection();
        $userRepo = new UserRepository($db);
        $formRepo = new FormRepository($db);
        $questionRepo = new QuestionRepository($db);

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

        // RÈGLE : Seul le propriétaire peut modifier le sondage et ses questions
        if ($form->getUserId() !== $user->getId()) {
            http_response_code(403);
            (new Error('Accès refusé', "Vous n'êtes pas autorisé à modifier ce sondage."))->show();
            return;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = filter_input(INPUT_POST, 'action');

            if ($action === 'update_name') {
                $newName = trim((string)filter_input(INPUT_POST, 'name'));
                if ($newName === '') {
                    $error = 'Le nom du sondage ne peut pas être vide.';
                } elseif (mb_strlen($newName) > 50) {
                    $error = 'Le nom du sondage ne peut pas dépasser 50 caractères.';
                } else {
                    if ($formRepo->updateFormName($formId, $newName, $user->getId())) {
                        $_SESSION['flash_message'] = 'Le titre du sondage a été mis à jour.';
                        header("Location: /survey/edit?id=$formId");
                        exit;
                    } else {
                        $error = 'Erreur lors de la mise à jour du titre.';
                    }
                }
            } elseif ($action === 'add_question') {
                $title = trim((string)filter_input(INPUT_POST, 'title'));
                $type = (string)filter_input(INPUT_POST, 'type');
                $choices = $_POST['choices'] ?? [];

                if ($title === '') {
                    $error = 'L\'intitulé de la question ne peut pas être vide.';
                } elseif (mb_strlen($title) > 50) {
                    $error = 'L\'intitulé ne peut pas dépasser 50 caractères.';
                } elseif (!in_array($type, [Question::TYPE_SELECTION, Question::TYPE_GRADE, Question::TYPE_FREETEXT], true)) {
                    $error = 'Type de question invalide.';
                } elseif ($type === Question::TYPE_SELECTION && count(array_filter(array_map('trim', (array)$choices))) < 2) {
                    $error = 'Une question à choix unique doit comporter au moins 2 options valides.';
                } else {
                    $order = $questionRepo->getNextOrder($formId);
                    $newQId = $questionRepo->createQuestion($formId, $title, $type, $order, (array)$choices);

                    if ($newQId !== false) {
                        $_SESSION['flash_message'] = 'Question ajoutée avec succès !';
                        header("Location: /survey/edit?id=$formId");
                        exit;
                    } else {
                        $error = 'Une erreur est survenue lors de l\'ajout de la question.';
                    }
                }
            } elseif ($action === 'delete_question') {
                $questionId = (int)filter_input(INPUT_POST, 'id_question', FILTER_VALIDATE_INT);
                if ($questionId > 0 && $questionRepo->deleteQuestion($questionId, $user->getId())) {
                    $_SESSION['flash_message'] = 'La question a été supprimée avec succès.';
                    header("Location: /survey/edit?id=$formId");
                    exit;
                } else {
                    $error = 'Impossible de supprimer cette question.';
                }
            }
        }

        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        // Rafraîchir les questions
        $questions = $questionRepo->getQuestionsByFormId($formId);
        $form = $formRepo->getFormById($formId);

        (new SurveyEdit(
            user: $user,
            form: $form,
            questions: $questions,
            message: $message,
            error: $error
        ))->show();
    }
}
