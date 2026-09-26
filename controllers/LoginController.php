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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            (new Login())->show();
            return;
        }

        $email = strtolower(trim((string)filter_input(INPUT_POST, 'email')));
        $password = (string)filter_input(INPUT_POST, 'password');

        $userRepository = new UserRepository(new DatabaseConnection());
        $user = $userRepository->checkLogin($email, $password);

        if ($user === null) {
            (new Error('ID INCORRECT'))->show();
            return;
        }

        session_start();
        $_SESSION['user_id'] = $user->getId();

        header('Location: /');
        exit;
    }
}
