<?php

namespace Controllers;

use _Assets\Includes\DatabaseConnection;
use models\FormRepository;
use models\UserRepository;
use Views\SurveyCreate;

class SurveyCreateController
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

        $user = $userRepo->findById($userId);
        if ($user === null) {
            unset($_SESSION['user_id']);
            header('Location: /login');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim((string)filter_input(INPUT_POST, 'name'));

            if ($name === '') {
                $error = 'Le titre du sondage ne peut pas être vide.';
            } elseif (mb_strlen($name) > 50) {
                $error = 'Le titre ne peut pas dépasser 50 caractères.';
            } else {
                $formId = $formRepo->createForm($name, $user->getId());
                if ($formId !== false && $formId > 0) {
                    $_SESSION['flash_message'] = "Sondage « $name » créé avec succès ! Ajoutez maintenant vos questions ci-dessous.";
                    header("Location: /survey/edit?id=$formId");
                    exit;
                } else {
                    $error = 'Une erreur est survenue lors de la création du sondage.';
                }
            }
        }

        (new SurveyCreate(user: $user, error: $error))->show();
    }
}
