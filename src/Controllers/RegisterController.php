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

        if ($this->userRepository->insertUser($email, $username, $password)) {
            header('Location: /login');
            exit;
        }
    }
}