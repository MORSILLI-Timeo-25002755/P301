<?php

use Controllers\loginController;

require '_assets/includes/autoloader.php';
require_once("controllers/loginController.php");
$controller = new loginController();
try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $controller->login();
    } else {
        $controller->execute();
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

/*
try {
    (new \Controllers\Homepage())->execute();
} catch (ControllerException $e) {
    (new \Views\Error($e->getMessage()))->show();
} */