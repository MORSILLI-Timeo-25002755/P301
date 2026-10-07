<?php

namespace Controllers;

class MentionsLegalesController
{
    public function execute(): void
    {
        (new \Views\MentionsLegales())->show();
    }
}