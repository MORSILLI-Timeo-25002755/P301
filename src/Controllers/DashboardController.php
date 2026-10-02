<?php

namespace Controllers;

use _assets\includes\HandleSessionActive;
use \Views\Dashboard;
use \Models\UserRepository;

class DashboardController extends HandleSessionActive
{
    public function execute(): void
    {
        parent::__construct();

        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $username = $this->user->getUsername();
            (new Dashboard($username))->show();
        }
    }
}