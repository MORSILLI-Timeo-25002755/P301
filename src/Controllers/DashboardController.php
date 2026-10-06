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
        parent::__construct();

        $this->requireLogin();

        try {
            $userRepository = new UserRepository(new DatabaseConnection());
            $formRepository = new FormRepository(new DatabaseConnection());
            $answerRepository = new AnswerRepository(new DatabaseConnection());
        } catch (PDOException $e) {
            (new \Views\Error("Erreur: BDD", "Connexion à la base de donnée impossible", "/"))->show();
            return;
        }

        if(isset($_SESSION['user_id'])) {
            $username = $this->user->getUsername();

            $nb_form = $formRepository->findNumberOfFormPerUser($_SESSION['user_id']);
            $nb_answer = $answerRepository->findNumberOfAnswerPerUser($_SESSION['user_id']);
            $user_mail = $userRepository->findById($_SESSION['user_id'])->getEmail();

            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

            $limit = 6;

            $total_pages = max(1, (int) ceil($nb_form / $limit));

            if ($page < 1) {
                $page = 1;
            }

            if ($page > $total_pages) {
                $page = $total_pages;
            }

            $offset = ($page - 1) * $limit;

            $infos_form = $formRepository->findFormInformations($_SESSION['user_id'], $limit, $offset);

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                (new Dashboard($username))->show($nb_form, $nb_answer, $user_mail, $infos_form, $page, $total_pages);
            }
        } else {
            (new \Views\Error('Erreur connexion', "Vous n'êtes pas connecté", '/login'))->show();
        }
    }
}