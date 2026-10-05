<?php

namespace Views;

class Error
{

    public function __construct(private string $title, private string $message, private string $page = '/')
    {
    }

    public function show(): void
    {
        begin_page('Erreur', '/css/error.css');
        ?>
        <main>
            <section class="error">
                <h1><?=$this->title?></h1>
                <p><?= htmlspecialchars($this->message) ?></p>
                <a class="button" href=<?= $this->page ?>>Retour à la page</a>
                <br>
                <a class="button" href="/">Accueil</a>
            </section>
        </main>
        <?php
    }
}