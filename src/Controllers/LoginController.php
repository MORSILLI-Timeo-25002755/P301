<?php

namespace Controllers;

use \Models\UserRepository;
use \Views\Error;
use \Views\Login;

class LoginController extends DatabaseController
{
    private UserRepository $userRepository;
    public function __construct() {
        parent::__construct();
        $this->userRepository = new UserRepository($this->db);
    }
    public function execute(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            (new Login())->show();
            return;
        }

        $email = strtolower(trim((string)filter_input(INPUT_POST, 'email')));
        $password = (string)filter_input(INPUT_POST, 'password');
        $user = $this->userRepository->checkLogin($email, $password);

        if ($user === null) {
            (new Error('Erreur: Connexion', 'Mot de passe ou email incorrect'))->show();
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->getId();

        header('Location: /dashboard');
        return;
    }
}
