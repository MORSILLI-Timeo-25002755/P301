<?php

namespace Controllers\Member;

require_once "_assets\includes\auth.php";

use _Assets\Includes\DatabaseConnection;
use models\UserRepository;
use Views\Member\Dashboard;

class dashboardController
{
    public function execute(): void
    {
        session_start();

        $userRepository = new UserRepository(new DatabaseConnection());
        $username = $userRepository->findById($_SESSION['user_id'])->getUsername();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            (new Dashboard($username))->show();
            return;
        }
    }
}