<?php

namespace Controllers;

use \_assets\Includes\DatabaseConnection;
use \_assets\Includes\HandleSessionActive;
use \Views\Dashboard;
use \Models\UserRepository;

class DashboardController extends HandleSessionActive
{
    public function __construct(?DatabaseConnection $db = null, ?UserRepository $userRepository = null)
    {
        parent::__construct($db, $userRepository);
    }

    public function execute(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $username = $this->user !== null ? $this->user->getUsername() : '';

            (new Dashboard($username ?? ''))->show();
        }
    }
}