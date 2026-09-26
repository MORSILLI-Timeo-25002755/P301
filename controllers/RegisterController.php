<?php

namespace Controllers;

use \Models\UserRepository;
use _Assets\Includes\DatabaseConnection;
use PDOException;

class RegisterController
{
    public function execute(): void
    {
        try {
            $userRepository = new UserRepository(new DatabaseConnection());
        } catch (PDOException $e) {
            (new \Views\Error("Connexion à la base de donnée impossible"))->show();
            return;
        }
        $notFilled = false;
        $validPassword = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = strtolower(trim((string) filter_input(INPUT_POST, 'email')));
            $username = trim((string) filter_input(INPUT_POST, 'username'));
            $password = trim((string) filter_input(INPUT_POST, 'pwd'));
            $confirmation = trim((string) filter_input(INPUT_POST, 'conf'));

            if ($email === '' || $username === '' || $password === '' || $confirmation === '') {
                $notFilled = true;
            } elseif ($password !== $confirmation) {
                $validPassword = false;
            } else {
                if($userRepository->insertUser($email, $username, $password)) {
                    header('Location: /login');
                    exit;
                } else {
                    (new \Views\Error('Email ou username déjà utilisé'))->show();
                }
                return;
            }
        }
        (new \Views\Register())->show($notFilled, $validPassword);
    }
}