<?php

namespace Views;

class Error
{

    public function __construct(private readonly String $message)
    {
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