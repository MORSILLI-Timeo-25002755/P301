<?php
function require_auth() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        header('location: /login');
        exit;
    }
}