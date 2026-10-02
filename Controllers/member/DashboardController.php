<?php

namespace controllers\member;

require_once "_assets\Includes\auth.php";

use _assets\Includes\DatabaseConnection;
use models\UserRepository;
use PDOException;
use views\member\Dashboard;

class DashboardController
{
    public function execute(): void
    {
        session_start();

        $userRepository = new UserRepository(new DatabaseConnection());
        if ($_SESSION['user_id']) {
            $username = $userRepository->findById($_SESSION['user_id'])->getUsername();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                (new Dashboard($username))->show();
            }
        } else {
            header('Location: /login');
        }
    }
}