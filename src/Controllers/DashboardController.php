<?php

namespace Controllers;

use \_assets\Includes\DatabaseConnection;
use \Views\Dashboard;
use \Models\UserRepository;

class DashboardController extends DatabaseController
{
    public function execute(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $username = $this->user->getUsername();
            (new Dashboard($username))->show();
        }
    }
}