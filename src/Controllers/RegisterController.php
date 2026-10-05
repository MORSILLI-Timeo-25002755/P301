<?php

namespace Controllers;

use \_assets\Includes\DatabaseConnection;
use JetBrains\PhpStorm\NoReturn;
use \Models\UserRepository;
use PDOException;
use Views\Error;

class RegisterController extends DatabaseController
{
    private UserRepository $userRepository;

    public function __construct(?DatabaseConnection $db = null, ?UserRepository $userRepository = null)
    {
        parent::__construct($db);
        $this->userRepository = $userRepository ?? new UserRepository($this->db);
    }

    public function execute(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // C'est ici qu'on génère le token pour l'envoyer à la vue
            $token = $this->generateCsrfToken();
            (new \Views\Register($token))->show(notFilled: false, validPassword: true);
            $this->terminate();
            return;
        }

        if (!$this->verifyCsrfToken($this->getPost('csrf_token'))) {
            (new Error('ERREUR DE SECURITE', 'Veuillez réessayer', '/register'))->show();
            $this->terminate();
            return;
        }

        $email = strtolower(trim((string)$this->getPost('email')));
        $username = trim((string)$this->getPost('username'));
        $password = (string)$this->getPost('pwd');
        $confirmation = (string)$this->getPost('conf');

        if ($email === '' || $username === '' || trim($password) === '' || $confirmation === '') {
            $token = $this->generateCsrfToken();
            (new \Views\Register($token))->show(notFilled: true, validPassword: true);
            $this->terminate();
            return;
        }

        if ($password !== $confirmation) {
            $token = $this->generateCsrfToken();
            (new \Views\Register($token))->show(notFilled: false, validPassword: false);
            $this->terminate();
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        // 8. Insertion en base de données
        if ($this->userRepository->insertUser($email, $username, $hashedPassword)) {
            $this->redirect('/login');
            return;
        }

        // 9. Échec : L'email ou le nom d'utilisateur existe déjà
        (new \Views\Error('Erreur: Inscription', 'Email ou username déjà utilisé', '/register'))->show();
        $this->terminate();
    }
}