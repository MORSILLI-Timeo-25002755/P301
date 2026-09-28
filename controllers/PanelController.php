<?php

namespace Controllers;

use _Assets\Includes\DatabaseConnection;
use models\FormRepository;
use models\UserRepository;
use Views\Panel;

class PanelController
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
        $dbConnection = new DatabaseConnection();
        $userRepository = new UserRepository($dbConnection);
        $formRepository = new FormRepository($dbConnection);

        $user = $userRepository->findById($userId);
        if ($user === null) {
            unset($_SESSION['user_id']);
            header('Location: /login');
            exit;
        }

        $error = null;

        // Traitement des actions formulaire (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = filter_input(INPUT_POST, 'action');

            if ($action === 'create') {
                $name = trim((string)filter_input(INPUT_POST, 'name'));

                if ($name === '') {
                    $error = 'Le nom du sondage ne peut pas être vide.';
                } elseif (mb_strlen($name) > 50) {
                    $error = 'Le nom du sondage ne peut pas dépasser 50 caractères.';
                } else {
                    if ($formRepository->createForm($name, $user->getId())) {
                        $_SESSION['flash_message'] = 'Le sondage "' . $name . '" a été créé avec succès !';
                        header('Location: /panel');
                        exit;
                    } else {
                        $error = 'Une erreur est survenue lors de la création du sondage.';
                    }
                }
            } elseif ($action === 'delete') {
                $formId = (int)filter_input(INPUT_POST, 'id_form', FILTER_VALIDATE_INT);

                if ($formId > 0 && $formRepository->deleteForm($formId, $user->getId())) {
                    $_SESSION['flash_message'] = 'Le sondage a été supprimé avec succès.';
                    header('Location: /panel');
                    exit;
                } else {
                    $error = 'Impossible de supprimer ce sondage.';
                }
            }
        }

        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        $forms = $formRepository->getFormsByUserId($user->getId());
        $totalAnswers = $formRepository->countTotalAnswersByUserId($user->getId());

        (new Panel(
            user: $user,
            forms: $forms,
            totalAnswers: $totalAnswers,
            message: $message,
            error: $error
        ))->show();
    }
}
