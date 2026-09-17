<?php
namespace Controllers;

class Homepage
{
    public function execute(): void
    {
        (new \Views\Homepage)->show();
    }
}