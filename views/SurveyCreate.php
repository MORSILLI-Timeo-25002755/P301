<?php

namespace Views;

use models\Users;

class SurveyCreate
{
    public function __construct(
        private Users $user,
        private ?string $error = null
    ) {}

    public function show(): void
    {
        begin_page('Créer un sondage', '_assets/css/survey.css');
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
                <span class="badge">Création</span>
                <h1>Créer un nouveau sondage</h1>
                <p class="description">Donnez un titre à votre sondage. Vous pourrez ensuite y ajouter autant de questions que vous le souhaitez (choix unique, notation, texte libre).</p>
                <div class="header-actions">
                    <a href="/panel" class="button button-secondary">&#8592; Retour au tableau de bord</a>
                </div>
            </header>

            <?php if ($this->error): ?>
                <p class="alert alert-error" role="alert"><?= htmlspecialchars($this->error) ?></p>
            <?php endif; ?>

            <section class="card">
                <h2>Informations générales</h2>
                <p class="description" style="margin-bottom: 20px;">
                    Renseignez le nom de votre enquête avant de configurer vos questions.
                </p>

                <form method="POST" action="/survey/create">
                    <div class="form-group">
                        <label for="survey-name">Titre du sondage (50 caractères max) :</label>
                        <input
                            type="text"
                            id="survey-name"
                            name="name"
                            class="form-input"
                            placeholder="Ex : Évaluation de la formation, Sondage d'équipe..."
                            maxlength="50"
                            required
                            autofocus
                        >
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 24px; gap: 12px;">
                        <a href="/panel" class="button button-secondary">Annuler</a>
                        <button type="submit" class="button button-primary">
                            Créer et ajouter des questions &#8594;
                        </button>
                    </div>
                </form>
            </section>
        </main>
        <?php
    }
}
