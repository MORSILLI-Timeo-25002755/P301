<?php

namespace Views;

use models\Form;
use models\Users;

class SurveyList
{
    /**
     * @param Form[] $forms
     * @param array<int, bool> $votedFormIds [form_id => true]
     */
    public function __construct(
        private Users $user,
        private array $forms = [],
        private array $votedFormIds = []
    ) {}

    public function show(): void
    {
        begin_page('Explorer les sondages', '_assets/css/survey.css');
        ?>
        <header class="navbar">
            <div class="navbar-container">
                <a href="/panel" class="navbar-brand">Sondages<strong>App</strong></a>
                <nav class="navbar-nav">
                    <a href="/surveys" class="nav-link" style="color: #4f46e5; font-weight: bold;">Tous les sondages</a>
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
                <span class="badge">Communauté</span>
                <h1>Explorer les sondages</h1>
                <p class="description">Découvrez les sondages créés par la communauté, participez aux votes ou consultez vos propres sondages.</p>

                <div class="header-actions">
                    <a href="/survey/create" class="button button-primary">+ Créer un sondage</a>
                    <a href="/panel" class="button button-secondary">&#8592; Mon tableau de bord</a>
                </div>
            </header>

            <?php if (empty($this->forms)): ?>
                <div class="card empty-state">
                    <div class="empty-icon">&#128203;</div>
                    <p class="empty-title">Aucun sondage pour le moment</p>
                    <p class="empty-desc">Soyez le premier à lancer une enquête sur la plateforme !</p>
                    <div style="margin-top: 16px;">
                        <a href="/survey/create" class="button button-primary">Créer mon premier sondage</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="surveys-grid">
                    <?php foreach ($this->forms as $form): ?>
                        <?php
                        $isOwner = $form->getUserId() === $this->user->getId();
                        $hasVoted = isset($this->votedFormIds[$form->getId()]);
                        ?>
                        <article class="survey-tile">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 8px;">
                                    <h2 class="survey-tile-title"><?= htmlspecialchars($form->getName()) ?></h2>
                                    <?php if ($isOwner): ?>
                                        <span class="badge-tag" style="background: #e0e7ff; color: #4338ca;">Votre sondage</span>
                                    <?php elseif ($hasVoted): ?>
                                        <span class="badge-tag" style="background: #f0fdf4; color: #16a34a;">Voté &#10004;</span>
                                    <?php endif; ?>
                                </div>

                                <p class="survey-tile-author">
                                    Par <strong><?= htmlspecialchars($form->getAuthorUsername() ?: 'Utilisateur #' . $form->getUserId()) ?></strong>
                                </p>
                            </div>

                            <div class="survey-tile-footer">
                                <span style="font-size: 13px; color: #64748b;">
                                    <strong><?= $form->getQuestionsCount() ?></strong> Q • <strong><?= $form->getAnswersCount() ?></strong> R
                                </span>

                                <div>
                                    <?php if ($isOwner): ?>
                                        <a href="/survey/stats?id=<?= $form->getId() ?>" class="button button-secondary button-sm">
                                            Statistiques
                                        </a>
                                    <?php elseif ($hasVoted): ?>
                                        <a href="/survey/vote?id=<?= $form->getId() ?>" class="button button-secondary button-sm" style="color: #16a34a;">
                                            Voir mon vote
                                        </a>
                                    <?php else: ?>
                                        <a href="/survey/vote?id=<?= $form->getId() ?>" class="button button-primary button-sm">
                                            Voter &#8594;
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
        <?php
    }
}
