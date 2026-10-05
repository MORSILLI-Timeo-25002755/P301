<?php

namespace Controllers;

use \_assets\Includes\HandleSessionActive;
use \_assets\Includes\DatabaseConnection;
use \Views\Dashboard;
use \Models\UserRepository;
use \Models\FormRepository;
use \Models\AnswerRepository;

class DashboardController extends HandleSessionActive
{
    public function execute(): void
    {
        session_start();

        parent::__construct();

        $this->requireLogin();

        try {
            $userRepository = new UserRepository(new DatabaseConnection());
            $formRepository = new FormRepository(new DatabaseConnection());
            $answerRepository = new AnswerRepository(new DatabaseConnection());
        } catch (PDOException $e) {
            (new \Views\Error("Erreur: BDD", "Connexion à la base de donnée impossible"))->show();
            return;
        }

        if(isset($_SESSION['user_id'])) {
            $username = $this->user->getUsername();

            $nb_form = $formRepository->findNumberOfFormPerUser($_SESSION['user_id']);
            $nb_answer = $answerRepository->findNumberOfAnswerPerUser($_SESSION['user_id']);
            $user_mail = $userRepository->findById($_SESSION['user_id'])->getEmail();

            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            if ($page < 1) {
                $page = 1;
            }

            $limit = 6;
            $offset = ($page - 1) * $limit;

            $totalPages = (int) ceil($nb_form / $limit);

            $infos_form = $formRepository->findFormInformations($_SESSION['user_id']);

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                (new Dashboard($username))->show($nb_form, $nb_answer, $user_mail, $infos_form, $page);
            }
        } else {
            (new \Views\Error('Erreur connexion', "Vous n'êtes pas connecté"))->show();
        }
    }
}