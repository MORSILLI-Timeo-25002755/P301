<?php

namespace Views;

class Error
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function show(): void
    {
        begin_page('Erreur', '_assets/css/error.css');
        ?>
        <main>
            <section class="error">
                <h1>Erreur</h1>
                <p><?= htmlspecialchars($this->message) ?></p>
                <a class="button" href="/">Retour à l'accueil</a>
            </section>
        </main>
        <?php
    }
}