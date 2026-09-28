<?php

namespace Controllers;

use _Assets\Includes\DatabaseConnection;
use models\AnswerRepository;
use models\FormRepository;
use models\UserRepository;
use Views\SurveyList;

class SurveyListController
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

        $forms = $formRepo->getAllAvailableForms();

        // Identifier les sondages auxquels l'utilisateur a déjà répondu
        $votedFormIds = [];
        foreach ($forms as $f) {
            if ($answerRepo->hasUserAnsweredForm($f->getId(), $user->getId())) {
                $votedFormIds[$f->getId()] = true;
            }
        }

        (new SurveyList(
            user: $user,
            forms: $forms,
            votedFormIds: $votedFormIds
        ))->show();
    }
}
