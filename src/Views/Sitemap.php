<?php

namespace Views;

use \Models\Users;

readonly class Sitemap
{
    public function __construct(private ?Users $user) {}

    public function show(): void
    {
        begin_page(
            'Plan du site',
            '/css/sitemap.css',
            link: 'https://apocalypsehorsemen.alwaysdata.net/sitemap',
            description: 'Retrouvez toutes les pages de HorseForm.'
        );
        ?>
        <main class="sitemap-page">
            <header class="sitemap-header">
                <div class="page-descriptor">HorseForm</div>
                <h1>Plan du site</h1>
                <p>Retrouvez rapidement les différentes pages du site.</p>
            </header>

            <section class="sitemap-grid" aria-label="Pages du site">
                <article class="sitemap-card">
                    <h2>Découvrir HorseForm</h2>
                    <ul>
                        <li><a href="/">Accueil</a></li>
                        <li><a href="/surveys">Tous les sondages</a></li>
                    </ul>
                </article>

                <?php if ($this->user !== null): ?>
                    <article class="sitemap-card">
                        <h2>Mon espace</h2>
                        <ul>
                            <li><a href="/dashboard">Tableau de bord</a></li>
                            <li><a href="/profile">Mon profil</a></li>
                            <li><a href="/logout">Déconnexion</a></li>
                        </ul>
                    </article>
                <?php else: ?>
                    <article class="sitemap-card">
                        <h2>Accéder à mon espace</h2>
                        <ul>
                            <li><a href="/login">Connexion</a></li>
                            <li><a href="/register">Inscription</a></li>
                            <li><a href="/forgot">Mot de passe oublié</a></li>
                        </ul>
                    </article>
                <?php endif; ?>
            </section>
        </main>
        <?php
        end_page();
    }
}
