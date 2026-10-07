<?php

namespace Views;

readonly class Surveys
{
    public function __construct(private array $surveys) {}

    public function show(): void
    {
        begin_page(
            'Tous les sondages',
            '/css/surveys.css',
            link: 'https://apocalypsehorsemen.alwaysdata.net/surveys',
            description: 'Découvrez tous les sondages disponibles sur HorseForm.'
        );
        ?>
        <main class="surveys-page">
            <header class="surveys-header">
                <div class="page-descriptor">HorseForm</div>
                <h1>Tous les sondages</h1>
                <p>Découvrez les sondages créés par la communauté.</p>
            </header>

            <?php if ($this->surveys === []): ?>
                <section class="empty-surveys">
                    <h2>Aucun sondage disponible</h2>
                    <p>Les premiers sondages apparaîtront bientôt ici.</p>
                </section>
            <?php else: ?>
                <section class="survey-grid" aria-label="Liste des sondages">
                    <?php foreach ($this->surveys as $survey): ?>
                        <article class="survey-card">
                            <h2><?= htmlspecialchars($survey['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <p class="survey-author">
                                Créé par <?= htmlspecialchars($survey['username'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="survey-questions">
                                <?= (int)$survey['nb_questions'] ?>
                                <?= (int)$survey['nb_questions'] > 1 ? 'questions' : 'question' ?>
                            </p>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </main>
        <?php
        end_page();
    }
}
