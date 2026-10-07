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
            (new \Views\Register($token))->show();
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
            (new \Views\Error('Erreur inscription', 'L\'un des champ est vide', '/register'))->show();
            exit;
        } else {
            $usr_in_bd = $this->userRepository->findByUsername($username);
            if ($usr_in_bd) {
                (new \Views\Error('Erreur username', 'Ce nom d\'utilisateur est deja pris', '/register'))->show();
                exit;
            }
        }



        $regex = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{12,}$/';

        if ($password !== $confirmation || !preg_match($regex, $password)) {
            (new \Views\Error('Erreur password', 'Mot de passe pas assez sécurisé', '/register'))->show();
            exit;
        }

        // 1. On vérifie le reCAPTCHA
        $recaptchaResponse = filter_input(INPUT_POST, 'g-recaptcha-response');

        if (empty($recaptchaResponse)) {
            (new \Views\Error('Erreur Captcha', 'Cochez la validation captcha', '/register'))->show();
            exit;
        }

        $secretKey = $_ENV['RECAPTCHA_SECRET_KEY'];

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
            header('Location: /login');
            exit;
        }

        (new \Views\Error('Erreur: Inscription', 'Email déjà utilisé', '/register'))->show();
        exit;
    }
}