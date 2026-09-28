<?php

namespace Views;

use models\Users;
use models\Form;

class Panel
{
    public function __construct(
        private Users $user,
        private array $forms = [],
        private int $totalAnswers = 0,
        private ?string $message = null,
        private ?string $error = null
    ) {}

    public function show(): void
    {
        begin_page('Mon Espace - Sondages', '_assets/css/panel.css');
        ?>
        <header class="navbar">
            <div class="navbar-container">
                <a href="/panel" class="navbar-brand">Sondages<strong>App</strong></a>
                <div class="navbar-user">
                    <a href="/surveys" class="nav-link">Explorer les sondages</a>
                    <span class="user-greeting">
                        Bonjour, <strong><?= htmlspecialchars($this->user->getUsername() ?: $this->user->getEmail()) ?></strong>
                    </span>
                    <a href="/logout" class="button button-logout">Se déconnecter</a>
                </div>
            </div>
        </header>

        <main class="panel-container">
            <?php if ($this->error): ?>
                <p class="alert alert-error" role="alert"><?= htmlspecialchars($this->error) ?></p>
            <?php endif; ?>

            <?php if ($this->message): ?>
                <p class="alert alert-success" role="status"><?= htmlspecialchars($this->message) ?></p>
            <?php endif; ?>

            <section class="panel-header">
                <span class="badge">Espace utilisateur</span>
                <h1>Tableau de bord</h1>
                <p class="description">Gérez vos sondages, consultez vos statistiques et créez de nouvelles enquêtes en quelques clics.</p>
            </section>

            <section class="stats-grid" aria-label="Statistiques rapides">
                <article class="stat-card">
                    <span class="stat-label">Sondages créés</span>
                    <span class="stat-value"><?= count($this->forms) ?></span>
                </article>

                <article class="stat-card">
                    <span class="stat-label">Réponses recueillies</span>
                    <span class="stat-value"><?= $this->totalAnswers ?></span>
                </article>

                <article class="stat-card">
                    <span class="stat-label">Compte connecté</span>
                    <span class="stat-value stat-user"><?= htmlspecialchars($this->user->getEmail()) ?></span>
                </article>
            </section>

            <section class="card create-section" aria-labelledby="create-title">
                <h2 id="create-title">Créer un nouveau sondage</h2>
                <p class="section-desc">Donnez un titre clair à votre sondage pour commencer à y associer vos questions.</p>

                <form method="POST" action="/panel" class="create-form">
                    <input type="hidden" name="action" value="create">
                    <div class="form-row">
                        <input
                            type="text"
                            name="name"
                            placeholder="Ex : Sondage de satisfaction 2026..."
                            maxlength="50"
                            required
                            autocomplete="off"
                        >
                        <button type="submit" class="button button-primary">Créer le sondage</button>
                    </div>
                </form>
            </section>

            <section class="card forms-section" aria-labelledby="forms-title">
                <div class="forms-header">
                    <h2 id="forms-title">Mes sondages</h2>
                    <span class="badge-count"><?= count($this->forms) ?> sondage<?= count($this->forms) > 1 ? 's' : '' ?></span>
                </div>

                <?php if (empty($this->forms)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">&#128203;</div>
                        <p class="empty-title">Vous n'avez pas encore créé de sondage</p>
                        <p class="empty-desc">Utilisez le formulaire ci-dessus pour lancer votre tout premier sondage !</p>
                    </div>
                <?php else: ?>
                    <div class="forms-grid">
                        <?php foreach ($this->forms as $form): ?>
                            <article class="survey-card">
                                <div class="survey-info">
                                    <h3 class="survey-title"><?= htmlspecialchars($form->getName()) ?></h3>
                                    <div class="survey-meta">
                                        <span class="meta-tag">
                                            <strong><?= $form->getQuestionsCount() ?></strong> question<?= $form->getQuestionsCount() > 1 ? 's' : '' ?>
                                        </span>
                                        <span class="meta-separator">•</span>
                                        <span class="meta-tag">
                                            <strong><?= $form->getAnswersCount() ?></strong> réponse<?= $form->getAnswersCount() > 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="survey-actions">
                                    <a href="/survey/edit?id=<?= $form->getId() ?>" class="button-action button-action-primary" title="Modifier les questions">
                                        &#9998; Questions
                                    </a>
                                    <a href="/survey/stats?id=<?= $form->getId() ?>" class="button-action" title="Consulter les statistiques">
                                        &#128202; Statistiques
                                    </a>
                                    <a href="/survey/vote?id=<?= $form->getId() ?>" class="button-action" title="Lien de vote à partager" target="_blank">
                                        &#128279; Lien votant
                                    </a>
                                    <form method="POST" action="/panel" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer le sondage &quot;<?= htmlspecialchars(addslashes($form->getName())) ?>&quot; ?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id_form" value="<?= $form->getId() ?>">
                                        <button type="submit" class="button-danger" title="Supprimer ce sondage">Supprimer</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
        <?php
    }
}
