<?php
namespace Controllers;

class HomepageController extends DatabaseController
{
    public function execute(): void
    {
        (new \Views\Homepage)->show();
    }
}