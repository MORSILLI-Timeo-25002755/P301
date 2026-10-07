<?php

namespace Controllers;

class MentionLegalesController
{
    public function execute(): void
    {
        (new \Views\MentionLegales)->show();
    }
}