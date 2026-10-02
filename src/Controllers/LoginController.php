<?php

namespace Controllers;

use \_assets\Includes\DatabaseConnection;
use \Models\UserRepository;
use \Views\Error;
use \Views\Login;

class LoginController extends DashboardController
{
    public function execute(): void
    {

        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
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

        header('Location: /dashboard');
        exit;
    }
}
