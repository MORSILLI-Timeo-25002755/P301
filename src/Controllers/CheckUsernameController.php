<?php

namespace Controllers;

use Models\UserRepository;

class CheckUsernameController extends DatabaseController
{
    public function execute(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $csrfToken = filter_input(INPUT_POST, 'csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            echo json_encode(['error' => 'Invalid CSRF token']);
            exit;
        }

        $username = trim((string)filter_input(INPUT_POST, 'username'));
        if ($username === '') {
            echo json_encode(['available' => false]);
            exit;
        }

        $userRepository = new UserRepository($this->db);

        $user = $userRepository->findByUsername($username);
        if ($user === null) {
            echo json_encode(['available' => true]);
        } else {
            echo json_encode(['available' => false]);
        }
        exit;
    }
}