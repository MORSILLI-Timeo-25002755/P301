<?php
namespace Controllers;

class Login
{
    public function execute(): void
    {
        (new \Views\Login)->show();
    }
}