<?php

namespace Controllers;

use _Assets\Includes\DatabaseConnection;
use \Models\UserRepository;
use \Models\Users;

abstract class DatabaseController // Une ligne pour l'instant, mais préférence sémantique + plus pratique si jamais on veut des logs
{
    protected DatabaseConnection $db;
    public function __construct(){
        $this->db = new DatabaseConnection();
    }
}