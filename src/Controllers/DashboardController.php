<?php

namespace Controllers;

use \_assets\Includes\DatabaseConnection;
use \Views\Dashboard;
use \Models\UserRepository;

class DashboardController
{
    public function execute(): void
    {
        session_start();

        if(isset($_SESSION['user_id'])) {
            try {

                $db = new DatabaseConnection();
                $userRepository = new UserRepository($db);

                $user = $userRepository->findById($_SESSION['user_id']);

                if (!$user) {
                    session_destroy();
                    (new \Views\Error('Erreur utilisateur', 'L\'utilisateur n\'existe pas'))->show();
                }

                $username = $user->getUsername();

                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    (new Dashboard($username))->show();
                } else {
                (new \Views\Error('Erreur connexion', "Vous n'êtes pas connecté"))->show();
            }
            }catch (\Exception $e) {
                (new \Views\Error('Erreur système', "Une erreur est survenue lors du chargement de votre tableau de bord."))->show();
            }
        } else {
            (new \Views\Error('Erreur connexion', "Vous n'êtes pas connecté"))->show();
        }

    }
}