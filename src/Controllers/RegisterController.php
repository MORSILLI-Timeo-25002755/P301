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

        // 1. On vérifie le reCAPTCHA
        $recaptchaResponse = filter_input(INPUT_POST, 'g-recaptcha-response');

        if (empty($recaptchaResponse)) {
            (new \Views\Error('Erreur Captcha', 'Cochez la validation captcha', '/register'))->show();
            exit;
        }

        $secretKey = $_ENV['RECAPTCHA_SECRET_KEY'] ?? getenv('RECAPTCHA_SECRET_KEY');

        $data = http_build_query([
            'secret' => $secretKey,
            'response' => $recaptchaResponse
        ]);

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $data,
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);

        $googleResponse = file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify',
            false,
            $context
        );

        $responseData = json_decode($googleResponse);

        if (!$responseData || !$responseData->success) {
            (new \Views\Error('Erreur: Captcha', 'Echec de la vérification anti robot', '/register'))->show();
            exit;
        }

        if ($this->userRepository->insertUser($email, $username, $password)) {
            $user = $this->userRepository->checkLogin($email, $password);
            $_SESSION['user_id'] = $user->getId();
            header('Location: /dashboard');
            exit;
        }

        (new \Views\Error('Erreur: Inscription', 'Email déjà utilisé', '/register'))->show();
        exit;
    }
}