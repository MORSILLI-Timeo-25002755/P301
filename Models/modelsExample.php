<?php
namespace models; // PSR-12: head blocks must be separated by a single blank line

use Model\Post\DatabaseException;
use Model\Post\Includes;
use Model\Post\PDO;
use Model\Post\Post;

class PostRepository { // PSR-12: opening brace next line

    public function __construct(private Includes\Database\DatabaseConnection $connection) {}

    public function getPosts(): array {
        if (!$statement = $this->connection->getConnection()->query('SELECT id, title, content, creation_date FROM posts ORDER BY creation_date DESC LIMIT 0, 5')) {
            throw new DatabaseException('Wrong');
        }
        $posts = [];
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
        $post = new Post($row->id, $row->title, $row->creation_date, $row->content);
        $posts[] = $post;
        }
        return $posts;
    }
}