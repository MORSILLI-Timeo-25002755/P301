<?php

namespace Controllers;

class Login
{
    public function execute(): void
    {
        (new \Views\login())->show();
    }
    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'];
            $pwd = $_POST['pwd'];

            $model = new loginModel();
            if ($model->checkLogin($email, $pwd)) {
                (new \Controllers\Homepage())->execute();
            } else {
                echo "ID incorrect";
                $this->execute();
            }
        }
    }
}
