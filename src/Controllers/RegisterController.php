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

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new UserRepository($this->db);
    }

    public function execute(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // C'est ici qu'on génère le token pour l'envoyer à la vue
            $token = $this->generateCsrfToken();
            (new \Views\Register($token))->show(notFilled: false, validPassword: true);
            exit;
        }

        if (!$this->verifyCsrfToken(filter_input(INPUT_POST, 'csrf_token'))) {
            (new Error('ERREUR DE SECURITE', 'Veuillez réessayer', '/register'))->show();
            exit;
        }

        $email = strtolower(trim((string)filter_input(INPUT_POST, 'email')));
        $username = trim((string)filter_input(INPUT_POST, 'username'));
        $password = (string)filter_input(INPUT_POST, 'pwd');
        $confirmation = (string)filter_input(INPUT_POST, 'conf');

        if ($email === '' || $username === '' || trim($password) === '' || $confirmation === '') {
            $token = $this->generateCsrfToken();
            (new \Views\Register($token))->show(notFilled: true, validPassword: true);
            exit;
        }

        if ($password !== $confirmation) {
            $token = $this->generateCsrfToken();
            (new \Views\Register($token))->show(notFilled: false, validPassword: false);
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        // 8. Insertion en base de données
        if ($this->userRepository->insertUser($email, $username, $hashedPassword)) {
            header('Location: /login');
            exit;
        }

        // 9. Échec : L'email ou le nom d'utilisateur existe déjà
        (new \Views\Error('Erreur: Inscription', 'Email ou username déjà utilisé', '/register'))->show();
        exit;
    }
}