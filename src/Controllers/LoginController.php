<?php

namespace Controllers;

use \_assets\Includes\DatabaseConnection;
use \Models\UserRepository;
use \Views\Error;
use \Views\Login;

class LoginController extends DatabaseController
{
    private UserRepository $userRepository;

    public function __construct(?DatabaseConnection $db = null, ?UserRepository $userRepository = null) {
        parent::__construct($db);
        $this->userRepository = $userRepository ?? new UserRepository($this->db);
    }

    public function execute(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $token = $this->generateCsrfToken();
            (new Login($token))->show();
            $this->terminate();
            return;
        }

        if (!$this->verifyCsrfToken($this->getPost('csrf_token'))){
            (new Error('ERREUR DE SECURITE', 'Veuillez réessayer', '/login'))->show();
            $this->terminate();
            return;
        }

        $email = strtolower(trim((string)$this->getPost('email')));
        $password = (string)$this->getPost('password');
        $user = $this->userRepository->checkLogin($email, $password);

        if ($user === null) {
            $this->logSecurity('CONNEXION ECHOUEE', 'Tentative de connexion');
            (new Error('Erreur: Connexion', 'Mot de passe ou email incorrect', '/login'))->show();
            $this->terminate();
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }
        $_SESSION['user_id'] = $user->getId();

        $this->redirect('/dashboard');
    }
}
