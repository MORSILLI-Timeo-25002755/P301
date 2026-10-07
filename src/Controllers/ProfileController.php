<?php

namespace Controllers;

use \_assets\Includes\HandleSessionActive;
use \Models\UserRepository;
use \Views\Profile;
use \Views\Error;

class ProfileController extends HandleSessionActive
{
    private UserRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new UserRepository($this->db);
    }

    public function execute(): void
    {
        $token = $this->generateCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            (new Profile($this->user, $token))->show();
            return;
        }

        if (!$this->verifyCsrfToken(filter_input(INPUT_POST, 'csrf_token'))) {
            (new Error('ERREUR DE SECURITE', 'Veuillez réessayer', '/profile'))->show();
            return;
        }

        $action = filter_input(INPUT_POST, 'action');
        if ($action === 'delete') {
            $this->userRepository->deleteUser($this->user->getId());
            unset($_SESSION['user_id']);
            session_destroy();
            header('Location: /');
            exit;
        }

        if ($action !== 'update') {
            (new Error('Erreur profil', 'Action inconnue.', '/profile'))->show();
            return;
        }

        $email = strtolower(trim((string)filter_input(INPUT_POST, 'email')));
        $username = trim((string)filter_input(INPUT_POST, 'username'));
        $password = (string)filter_input(INPUT_POST, 'password');
        $confirmation = (string)filter_input(INPUT_POST, 'password_confirmation');

        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL)
            || $username === ''
            || strlen($email) > 50
            || strlen($username) > 50
        ) {
            (new Profile($this->user, $token, 'Veuillez renseigner un e-mail et un nom d’utilisateur valides (50 caractères maximum).'))->show();
            return;
        }

        if ($password !== '' && ($password !== $confirmation || strlen($password) < 12)) {
            (new Profile($this->user, $token, 'Le nouveau mot de passe doit contenir au moins 12 caractères et être confirmé.'))->show();
            return;
        }

        $updated = $this->userRepository->updateProfile(
            $this->user->getId(),
            $email,
            $username,
            $password === '' ? null : $password
        );

        if (!$updated) {
            (new Profile($this->user, $token, 'Ce nom d’utilisateur est déjà utilisé.'))->show();
            return;
        }

        $this->user = $this->userRepository->findById($this->user->getId());
        (new Profile($this->user, $token, 'Vos informations ont été mises à jour.', true))->show();
    }
}
