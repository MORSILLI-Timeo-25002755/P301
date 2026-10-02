<?php

namespace Controllers;

use \_assets\Includes\DatabaseConnection;
use \Models\UserRepository;
use PDOException;

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
        $notFilled = false;
        $validPassword = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = strtolower(trim((string) filter_input(INPUT_POST, 'email')));
            $username = trim((string) filter_input(INPUT_POST, 'username'));
            $password = (string) filter_input(INPUT_POST, 'pwd');
            $confirmation = trim((string) filter_input(INPUT_POST, 'conf'));

            if ($email === '' || $username === '' || trim($password) === '' || $confirmation === '') {
                $notFilled = true;
            } elseif ($password !== $confirmation) {
                $validPassword = false;
            } else {
                if($this->userRepository->insertUser($email, $username, $password)) {
                    header('Location: /login');
                    exit;
                } else {
                    (new \Views\Error('Erreur: Connexion','Email ou username déjà utilisé'))->show();
                }
                return;
            }
        }
        (new \Views\Register())->show($notFilled, $validPassword);
    }
}