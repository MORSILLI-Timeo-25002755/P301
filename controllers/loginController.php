<?php

namespace Controllers;

use models\loginModel;

require_once("models/loginModel.php");
require_once("views/loginView.php");

class loginController
{
    public function execute(): void
    {
        (new \Views\loginView())->show();
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