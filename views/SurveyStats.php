<?php

namespace Views;

use models\Form;
use models\Users;

class SurveyStats
{
    /**
     * @param array{
     *     total_respondents: int,
     *     total_answers: int,
     *     questions: array
     * } $stats
     */
    public function __construct(
        private Users $user,
        private Form $form,
        private array $stats
    ) {}

    public function show(): void
    {
        $surveyName = htmlspecialchars($this->form->getName());
        begin_page("Statistiques - $surveyName", '_assets/css/survey.css');

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $voteUrl = "$protocol://$host/survey/vote?id=" . $this->form->getId();
        ?>
        <header class="navbar">
            <div class="navbar-container">
                <a href="/panel" class="navbar-brand">Sondages<strong>App</strong></a>
                <nav class="navbar-nav">
                    <a href="/surveys" class="nav-link">Tous les sondages</a>
                    <a href="/panel" class="nav-link">Mon tableau de bord</a>
                    <span class="user-greeting">
                        <strong><?= htmlspecialchars($this->user->getUsername() ?: $this->user->getEmail()) ?></strong>
                    </span>
                    <a href="/logout" class="button-logout">Déconnexion</a>
                </nav>
            </div>
        </header>

        <main class="survey-container">
            <header class="page-header">
                <span class="badge">Espace Propriétaire • Statistiques</span>
                <h1>Statistiques : <?= $surveyName ?></h1>
                <p class="description">Consultez l'ensemble des réponses recueillies et les analyses détaillées question par question.</p>

                <div class="header-actions">
                    <a href="/survey/edit?id=<?= $this->form->getId() ?>" class="button button-secondary">
                        &#9998; Gérer les questions
                    </a>
                    <a href="/panel" class="button button-secondary">
                        &#8592; Tableau de bord
                    </a>
                </div>
            </header>

            <!-- Barre de partage rapide -->
            <div class="card" style="padding: 16px 20px; margin-bottom: 24px;">
                <p style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px;">
                    Lien public pour les votants (non-propriétaires) :
                </p>
                <div class="share-bar">
                    <input
                        type="text"
                        readonly
                        value="<?= htmlspecialchars($voteUrl) ?>"
                        class="share-input"
                        id="share-link-input"
                    >
                    <button
                        type="button"
                        class="button button-primary button-sm"
                        onclick="navigator.clipboard.writeText(document.getElementById('share-link-input').value).then(() => { this.innerText = 'Copié !'; setTimeout(() => this.innerText = 'Copier', 2000); });"
                    >
                        Copier
                    </button>
                    <a href="/survey/vote?id=<?= $this->form->getId() ?>" target="_blank" class="button button-secondary button-sm">
                        Tester
                    </a>
                </div>
            </div>

            <!-- Grille d'indicateurs globaux -->
            <section class="stats-grid" aria-label="Indicateurs clés">
                <article class="stat-card">
                    <span class="stat-label">Participants distincts</span>
                    <span class="stat-value"><?= $this->stats['total_respondents'] ?></span>
                </article>

                <article class="stat-card">
                    <span class="stat-label">Total réponses enregistrées</span>
                    <span class="stat-value"><?= $this->stats['total_answers'] ?></span>
                </article>

                <article class="stat-card">
                    <span class="stat-label">Nombre de questions</span>
                    <span class="stat-value"><?= count($this->stats['questions']) ?></span>
                </article>
            </section>

            <!-- Résultats détaillés par question -->
            <?php if (empty($this->stats['questions'])): ?>
                <div class="card empty-state">
                    <div class="empty-icon">&#128203;</div>
                    <p class="empty-title">Ce sondage ne possède aucune question</p>
                    <p class="empty-desc">Ajoutez des questions pour commencer à recueillir des votes et afficher des statistiques.</p>
                    <div style="margin-top: 16px;">
                        <a href="/survey/edit?id=<?= $this->form->getId() ?>" class="button button-primary">
                            Ajouter des questions
                        </a>
                    </div>
                </div>
            <?php elseif ($this->stats['total_respondents'] === 0): ?>
                <div class="card empty-state" style="margin-bottom: 24px;">
                    <div class="empty-icon">&#128202;</div>
                    <p class="empty-title">Aucune réponse pour l'instant</p>
                    <p class="empty-desc">Partagez le lien de votre sondage avec d'autres utilisateurs pour obtenir vos premiers résultats !</p>
                </div>
            <?php endif; ?>

            <?php foreach ($this->stats['questions'] as $index => $qStat): ?>
                <?php
                $q = $qStat['question'];
                $totalResp = $qStat['total_responses'];
                ?>
                <section class="question-card">
                    <header class="question-header">
                        <div class="question-title-area">
                            <span class="question-order">Q<?= $index + 1 ?></span>
                            <h3><?= htmlspecialchars($q->getTitle()) ?></h3>
                        </div>
                        <div class="question-meta">
                            <span class="badge-tag <?= match($q->getType()) { 'selection' => 'badge-selection', 'grade' => 'badge-grade', 'freetext' => 'badge-freetext', default => '' } ?>">
                                <?= $q->getTypeLabel() ?>
                            </span>
                            <span class="badge-tag">
                                <strong><?= $totalResp ?></strong> réponse<?= $totalResp > 1 ? 's' : '' ?>
                            </span>
                        </div>
                    </header>

                    <?php if ($q->isSelection()): ?>
                        <?php $maxVotes = $qStat['data']['max_votes'] ?? 0; ?>
                        <div class="choices-stats-list">
                            <?php if (empty($qStat['data']['choices'])): ?>
                                <p style="color: #64748b; font-size: 14px;">Aucun choix configuré pour cette question.</p>
                            <?php else: ?>
                                <?php foreach ($qStat['data']['choices'] as $choice): ?>
                                    <?php $isWinner = ($choice['votes'] > 0 && $choice['votes'] === $maxVotes); ?>
                                    <div class="choice-stat-item <?= $isWinner ? 'choice-stat-winner' : '' ?>">
                                        <div class="choice-stat-labels">
                                            <span class="choice-stat-title">
                                                <?= htmlspecialchars($choice['title']) ?>
                                                <?php if ($isWinner): ?>
                                                    <span style="color: #10b981; font-weight: bold; font-size: 12px; margin-left: 6px;">&#10004; En tête</span>
                                                <?php endif; ?>
                                            </span>
                                            <span class="choice-stat-numbers">
                                                <strong><?= $choice['votes'] ?></strong> vote<?= $choice['votes'] > 1 ? 's' : '' ?> (<?= $choice['percentage'] ?>%)
                                            </span>
                                        </div>
                                        <div class="progress-bar-bg">
                                            <div class="progress-bar-fill" style="width: <?= $choice['percentage'] ?>%;"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($q->isGrade()): ?>
                        <div class="grade-summary">
                            <div class="grade-avg-box">
                                <span class="grade-avg-number">
                                    <?= $qStat['data']['average'] !== null ? number_format($qStat['data']['average'], 1) : '-' ?>
                                </span>
                                <span class="grade-avg-max">sur 5.0 &#9733;</span>
                                <span style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                    Min: <?= $qStat['data']['min'] ?? '-' ?> | Max: <?= $qStat['data']['max'] ?? '-' ?>
                                </span>
                            </div>

                            <div class="grade-distribution">
                                <?php for ($s = 5; $s >= 1; $s--): ?>
                                    <?php
                                    $dist = $qStat['data']['distribution'][$s] ?? ['count' => 0, 'percentage' => 0];
                                    ?>
                                    <div class="grade-dist-row">
                                        <span class="grade-dist-star"><?= $s ?> &#9733;</span>
                                        <div class="progress-bar-bg grade-dist-bar">
                                            <div class="progress-bar-fill" style="width: <?= $dist['percentage'] ?>%; background: #f59e0b;"></div>
                                        </div>
                                        <span class="grade-dist-count"><?= $dist['count'] ?> (<?= $dist['percentage'] ?>%)</span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                    <?php elseif ($q->isFreeText()): ?>
                        <div class="freetext-list">
                            <?php if (empty($qStat['data']['responses'])): ?>
                                <p style="color: #64748b; font-size: 14px; font-style: italic;">
                                    Aucune réponse textuelle enregistrée pour l'instant.
                                </p>
                            <?php else: ?>
                                <?php foreach ($qStat['data']['responses'] as $resp): ?>
                                    <article class="freetext-item">
                                        <p class="freetext-author">&#128100; <?= htmlspecialchars($resp['username']) ?></p>
                                        <p class="freetext-content"><?= htmlspecialchars($resp['text']) ?></p>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </main>
        <?php
    }
}
