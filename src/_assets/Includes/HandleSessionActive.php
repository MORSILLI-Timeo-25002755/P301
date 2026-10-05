<?php

namespace _assets\Includes;

use \Controllers\DatabaseController;
use \Models\UserRepository;
use \Models\Users;

abstract class HandleSessionActive extends DatabaseController
{
    protected ?Users $user = null;

    public function __construct()
    {
        parent::__construct();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->requireLogin();
    }

    protected function requireLogin(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->logSecurity('ACCÈS REFUSÉ', 'Tentative d\'accès sans session.');
            header('Location: /login');
            exit;
        }

        $userRepository = new UserRepository($this->db);
        $this->user = $userRepository->findById($_SESSION['user_id']);

        if (!$this->user) {
            session_destroy();
            $this->logSecurity('SESSION INVALIDE', "ID {$_SESSION['user_id']} introuvable en base.");
            header('Location: /login');
            exit;
        }
    }
}