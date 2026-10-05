<?php

namespace Controllers;

use _assets\Includes\DatabaseConnection;
use Models\UserRepository;
use Models\Users;

abstract class DatabaseController // Classe parent de toutes les pages web qui requiert une connexion utilisateur pour être atteinte, évite de répéter du code.
{
    protected DatabaseConnection $db;
    protected ?Users $user;

    /**
     * @throws \Exception
     */
    public function __construct(){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $this->db = new DatabaseConnection();

        if (isset($_SESSION['user_id'])) {
            $userRepository = new UserRepository($this->db);
            $this->user = $userRepository->findById($_SESSION['user_id']);

            if (!$this->user) {
                session_destroy();
                throw new \Exception("Utilisateur invalide en session.");
            }
        }
    }

    protected function requireLogin(): void
    {
        if (!$this->user) {
            (new \Views\Error('Erreur connexion', "Vous n'êtes pas connecté"))->show();
            exit;
        }
    }
}