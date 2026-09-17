<?php
require '_assets/includes/autoloader.php';

try {
    (new \Controllers\Homepage())->execute();
} catch (ControllerException $e) {
    (new \Views\Error($e->getMessage()))->show();
}