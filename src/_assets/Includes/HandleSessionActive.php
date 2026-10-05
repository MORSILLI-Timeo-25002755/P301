<?php

namespace _assets\Includes;

use \Controllers\DatabaseController;
use \Models\UserRepository;
use \Models\Users;

abstract class HandleSessionActive extends DatabaseController
{
    protected ?Users $user = null;
    protected UserRepository $userRepository;

    public function __construct(?DatabaseConnection $db = null, ?UserRepository $userRepository = null)
    {
        parent::__construct($db);
        $this->userRepository = $userRepository ?? new UserRepository($this->db);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->requireLogin();
    }

    protected function requireLogin(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->logSecurity('ACCÈS REFUSÉ', 'Tentative d\'accès sans session.');
            $this->redirect('/login');
            return;
        }

        $this->user = $this->userRepository->findById((int)$_SESSION['user_id']);

        if (!$this->user) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            $this->logSecurity('SESSION INVALIDE', "ID {$_SESSION['user_id']} introuvable en base.");
            $this->redirect('/login');
            return;
        }
    }
}