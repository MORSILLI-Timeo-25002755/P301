<?php

namespace Views;

use models\Form;
use models\Question;
use models\Users;

class SurveyVote
{
    /**
     * @param Question[] $questions
     */
    public function __construct(
        private Users $user,
        private ?Form $form = null,
        private array $questions = [],
        private string $state = 'form', // 'form', 'owner_forbidden', 'already_voted', 'success', 'no_questions'
        private ?string $error = null,
        private ?string $message = null
    ) {}

    public function show(): void
    {
        $surveyName = $this->form ? htmlspecialchars($this->form->getName()) : 'Sondage';
        begin_page("Voter - $surveyName", '_assets/css/survey.css');
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
            <?php if ($this->error): ?>
                <p class="alert alert-error" role="alert"><?= htmlspecialchars($this->error) ?></p>
            <?php endif; ?>

            <?php if ($this->message): ?>
                <p class="alert alert-success" role="status"><?= htmlspecialchars($this->message) ?></p>
            <?php endif; ?>

            <?php if ($this->state === 'owner_forbidden'): ?>
                <div class="card empty-state">
                    <div class="empty-icon">&#128721;</div>
                    <h1 class="empty-title">Vous êtes le propriétaire de ce sondage</h1>
                    <p class="empty-desc">
                        Conformément aux règles du sondage, seul le public et les autres utilisateurs peuvent voter.<br>
                        En tant que créateur, vous avez accès aux résultats et statistiques en temps réel.
                    </p>
                    <div class="header-actions" style="justify-content: center; margin-top: 20px;">
                        <a href="/survey/stats?id=<?= $this->form->getId() ?>" class="button button-primary">
                            Consulter les statistiques
                        </a>
                        <a href="/survey/edit?id=<?= $this->form->getId() ?>" class="button button-secondary">
                            Modifier le sondage
                        </a>
                        <a href="/panel" class="button button-secondary">
                            Tableau de bord
                        </a>
                    </div>
                </div>

            <?php elseif ($this->state === 'already_voted'): ?>
                <div class="card empty-state">
                    <div class="empty-icon">&#9989;</div>
                    <h1 class="empty-title">Vous avez déjà voté pour ce sondage</h1>
                    <p class="empty-desc">
                        Votre vote pour « <strong><?= $surveyName ?></strong> » a déjà été pris en compte.<br>
                        Chaque utilisateur ne peut voter qu'une seule fois par sondage.
                    </p>
                    <div class="header-actions" style="justify-content: center; margin-top: 20px;">
                        <a href="/surveys" class="button button-primary">
                            Explorer d'autres sondages
                        </a>
                        <a href="/panel" class="button button-secondary">
                            Mon tableau de bord
                        </a>
                    </div>
                </div>

            <?php elseif ($this->state === 'success'): ?>
                <div class="card empty-state">
                    <div class="empty-icon">&#127881;</div>
                    <h1 class="empty-title">Merci pour votre participation !</h1>
                    <p class="empty-desc">
                        Vos réponses au sondage « <strong><?= $surveyName ?></strong> » ont été enregistrées avec succès.
                    </p>
                    <div class="header-actions" style="justify-content: center; margin-top: 20px;">
                        <a href="/surveys" class="button button-primary">
                            Participer à d'autres sondages
                        </a>
                        <a href="/panel" class="button button-secondary">
                            Retour à mon tableau de bord
                        </a>
                    </div>
                </div>

            <?php elseif ($this->state === 'no_questions'): ?>
                <div class="card empty-state">
                    <div class="empty-icon">&#9888;&#65039;</div>
                    <h1 class="empty-title">Aucune question disponible</h1>
                    <p class="empty-desc">
                        Le créateur de « <strong><?= $surveyName ?></strong> » n'a pas encore ajouté de questions à ce sondage.
                    </p>
                    <div class="header-actions" style="justify-content: center; margin-top: 20px;">
                        <a href="/surveys" class="button button-primary">
                            Voir d'autres sondages
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <!-- Formulaire de vote actif -->
                <header class="page-header">
                    <span class="badge">Participation au sondage</span>
                    <h1><?= $surveyName ?></h1>
                    <p class="description">
                        Créé par <strong><?= htmlspecialchars($this->form->getAuthorUsername() ?: 'Utilisateur #' . $this->form->getUserId()) ?></strong>
                        • <?= count($this->questions) ?> question<?= count($this->questions) > 1 ? 's' : '' ?>
                    </p>
                </header>

                <form method="POST" action="/survey/vote?id=<?= $this->form->getId() ?>" class="vote-form">
                    <?php foreach ($this->questions as $index => $question): ?>
                        <article class="vote-question-card">
                            <h2 class="vote-question-title">
                                <span class="question-order"><?= $index + 1 ?>.</span>
                                <?= htmlspecialchars($question->getTitle()) ?>
                            </h2>

                            <?php if ($question->isSelection()): ?>
                                <div class="vote-choices-list">
                                    <?php foreach ($question->getChoices() as $choice): ?>
                                        <label class="vote-choice-label">
                                            <input
                                                type="radio"
                                                name="answers[<?= $question->getId() ?>]"
                                                value="<?= $choice->getId() ?>"
                                                required
                                            >
                                            <span><?= htmlspecialchars($choice->getTitle()) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                            <?php elseif ($question->isGrade()): ?>
                                <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">
                                    Attribuez une note de 1 à 5 :
                                </p>
                                <div class="rating-group">
                                    <?php for ($score = 1; $score <= 5; $score++): ?>
                                        <div class="rating-item">
                                            <input
                                                type="radio"
                                                id="q_<?= $question->getId() ?>_s_<?= $score ?>"
                                                name="answers[<?= $question->getId() ?>]"
                                                value="<?= $score ?>"
                                                required
                                            >
                                            <label for="q_<?= $question->getId() ?>_s_<?= $score ?>" class="rating-label">
                                                <span><?= $score ?> &#9733;</span>
                                                <span class="rating-sub"><?= match($score) { 1 => 'Très décevant', 2 => 'Décevant', 3 => 'Moyen', 4 => 'Bien', 5 => 'Excellent' } ?></span>
                                            </label>
                                        </div>
                                    <?php endfor; ?>
                                </div>

                            <?php elseif ($question->isFreeText()): ?>
                                <textarea
                                    name="answers[<?= $question->getId() ?>]"
                                    class="vote-textarea"
                                    placeholder="Rédigez votre réponse ici..."
                                    rows="4"
                                    required
                                ></textarea>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>

                    <div class="vote-submit-row">
                        <button type="submit" class="button button-primary" style="padding: 14px 32px; font-size: 16px;">
                            Valider mon vote
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </main>
        <?php
    }
}
