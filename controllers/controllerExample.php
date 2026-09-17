<?php
namespace Controllers;
use Includes\Database\DatabaseConnection;
use Models\Post;

class Homepage
{
    public function execute(): void
    {
        $postRepository = new PostRepository(DatabaseConnection::getInstance());
        $posts = $postRepository->getPosts();
        (new \Views\Post($posts))->show();
    }
}