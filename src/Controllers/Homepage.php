<?php
namespace Controllers;

class Homepage
{
    public function execute(): void
    {
        session_start();
        (new \Views\Homepage)->show();
    }
}