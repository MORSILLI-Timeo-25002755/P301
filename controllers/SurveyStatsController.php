<?php

namespace Controllers;

use _Assets\Includes\DatabaseConnection;
use models\AnswerRepository;
use models\FormRepository;
use models\UserRepository;
use Views\Error;
use Views\SurveyStats;

class SurveyStatsController
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
        $formId = (int)($_GET['id'] ?? 0);

        if ($formId <= 0) {
            header('Location: /panel');
            exit;
        }

        $db = new DatabaseConnection();
        $userRepo = new UserRepository($db);
        $formRepo = new FormRepository($db);
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

        // RÈGLE : Seul le propriétaire du sondage peut consulter ses statistiques
        if ($form->getUserId() !== $user->getId()) {
            http_response_code(403);
            (new Error('Accès refusé', "Vous devez être le propriétaire du sondage « {$form->getName()} » pour accéder à ses statistiques."))->show();
            return;
        }

        $stats = $answerRepo->getSurveyStats($formId);

        (new SurveyStats(
            user: $user,
            form: $form,
            stats: $stats
        ))->show();
    }
}
