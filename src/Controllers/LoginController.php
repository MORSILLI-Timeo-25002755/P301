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

        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $token = $this->generateCsrfToken();
            (new Login($token))->show();
            exit;
        }

        if (!$this->verifyCsrfToken(filter_input(INPUT_POST, 'csrf_token'))){
            (new Error('ERREUR DE SECURITE', 'Veuillez réessayer', '/login'))->show();
            exit;
        }

        $email = strtolower(trim((string)filter_input(INPUT_POST, 'email')));
        $password = (string)filter_input(INPUT_POST, 'password');
        $user = $this->userRepository->checkLogin($email, $password);

        if ($user === null) {
            $this->logSecurity('CONNEXION ECHOUEE', 'Tentative de connexion');
            (new Error('Erreur: Connexion', 'Mot de passe ou email incorrect', '/login'))->show();
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->getId();

        header('Location: /dashboard');
        exit;
    }
}
