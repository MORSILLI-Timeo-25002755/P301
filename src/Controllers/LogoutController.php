<?php

namespace Controllers;

class LogoutController extends DataBaseController{
    public function execute() {
        unset($_SESSION['user_id']);

        session_destroy();
        header('Location: /login');
        exit;
    }
}