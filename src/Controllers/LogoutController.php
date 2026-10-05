<?php

namespace Controllers;

class LogoutController {
    public function execute() {
        session_destroy();
        header('Location: /login');
        exit;
    }
}