<?php

namespace Controllers;

use \Models\Users;
use \Views\Sitemap;

class SitemapController extends DatabaseController
{
    public function execute(): void
    {
        $user = null;

        if (isset($_SESSION['user_id'])) {
            $userRepository = new \Models\UserRepository($this->db);
            $user = $userRepository->findById((int)$_SESSION['user_id']);
        }

        (new Sitemap($user))->show();
    }
}
