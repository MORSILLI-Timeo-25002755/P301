<?php
namespace Controllers;

class Homepage extends DatabaseController
{
    public function execute(): void
    {
        (new \Views\Homepage)->show();
    }
}