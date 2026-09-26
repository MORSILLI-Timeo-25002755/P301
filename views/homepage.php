<?php

namespace Views;

class Homepage
{
    public function show(): void
    {
        begin_page('Accueil', '_assets/css/welcome.css');
        ?>
        <main>
            <header class="hero">
                <p class="badge">Création de sondages</p>

                <h1>
                    Créez des sondages.<br>
                    <strong>Obtenez des réponses.</strong>
                </h1>

                <p class="description">
                    Créez facilement vos sondages et partagez-les
                    pour recueillir rapidement les réponses de vos participants.
                </p>

                <nav aria-label="Actions principales">
                    <a href="/register" class="button button-primary">
                        Créer un compte
                    </a>

                    <a href="/login" class="button button-secondary">
                        Se connecter
                    </a>
                </nav>
            </header>

            <section aria-labelledby="features-title">
                <h2 id="features-title">Tout ce qu'il vous faut</h2>

                <article>
                    <header>
                        <h3>Créez</h3>
                    </header>

                    <p>
                        Créez vos sondages simplement et ajoutez
                        les questions dont vous avez besoin.
                    </p>
                </article>

                <article>
                    <header>
                        <h3>Partagez</h3>
                    </header>

                    <p>
                        Partagez facilement vos sondages avec
                        les personnes de votre choix.
                    </p>
                </article>

                <article>
                    <header>
                        <h3>Analysez</h3>
                    </header>

                    <p>
                        Consultez les réponses de vos participants
                        et obtenez rapidement les résultats.
                    </p>
                </article>
            </section>
        </main>
        <?php
    }
}