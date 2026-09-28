<?php

namespace Controllers;

use _Assets\Includes\DatabaseConnection;
use models\UserRepository;
use models\Users;
use Views\Login;
use Views\Error;

class LoginController
{
    public function execute(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            header('Location: /panel');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            (new Login())->show();
            return;
        }

        $email = strtolower(trim((string)filter_input(INPUT_POST, 'email')));
        $password = (string)filter_input(INPUT_POST, 'password');

        $userRepository = new UserRepository(new DatabaseConnection());
        $user = $userRepository->checkLogin($email, $password);

        if ($user === null) {
            (new Error('Erreur: Connexion', 'Mot de passe ou email incorrect'))->show();
            return;
        }

        $_SESSION['user_id'] = $user->getId();

        header('Location: /panel');
        exit;
    }
}
