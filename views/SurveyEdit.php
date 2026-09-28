<?php

namespace Views;

use models\Form;
use models\Question;
use models\Users;

class SurveyEdit
{
    /**
     * @param Question[] $questions
     */
    public function __construct(
        private Users $user,
        private Form $form,
        private array $questions = [],
        private ?string $message = null,
        private ?string $error = null
    ) {}

    public function show(): void
    {
        $surveyName = htmlspecialchars($this->form->getName());
        begin_page("Gestion des questions - $surveyName", '_assets/css/survey.css');

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
            <?php if ($this->error): ?>
                <p class="alert alert-error" role="alert"><?= htmlspecialchars($this->error) ?></p>
            <?php endif; ?>

            <?php if ($this->message): ?>
                <p class="alert alert-success" role="status"><?= htmlspecialchars($this->message) ?></p>
            <?php endif; ?>

            <header class="page-header">
                <span class="badge">Édition du sondage</span>
                <h1><?= $surveyName ?></h1>
                <p class="description">Configurez les questions de votre sondage, définissez leur type et ajoutez des options de réponse.</p>

                <div class="header-actions">
                    <a href="/survey/stats?id=<?= $this->form->getId() ?>" class="button button-primary">
                        &#128202; Voir les statistiques
                    </a>
                    <a href="/survey/vote?id=<?= $this->form->getId() ?>" class="button button-secondary" target="_blank">
                        &#128065; Aperçu du vote
                    </a>
                    <a href="/panel" class="button button-secondary">
                        &#8592; Tableau de bord
                    </a>
                </div>
            </header>

            <!-- Modifier le nom du sondage -->
            <section class="card">
                <h2>Modifier le titre du sondage</h2>
                <form method="POST" action="/survey/edit?id=<?= $this->form->getId() ?>" style="margin-top: 14px;">
                    <input type="hidden" name="action" value="update_name">
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <input
                            type="text"
                            name="name"
                            value="<?= $surveyName ?>"
                            class="form-input"
                            style="flex: 1; min-width: 250px;"
                            maxlength="50"
                            required
                        >
                        <button type="submit" class="button button-secondary">Enregistrer le titre</button>
                    </div>
                </form>
            </section>

            <!-- Formulaire d'ajout de question -->
            <section class="card" aria-labelledby="add-question-title">
                <h2 id="add-question-title">Ajouter une question</h2>
                <p class="description" style="margin-bottom: 20px;">
                    Sélectionnez un type de question pour personnaliser la réponse attendue des participants.
                </p>

                <form method="POST" action="/survey/edit?id=<?= $this->form->getId() ?>" id="add-question-form">
                    <input type="hidden" name="action" value="add_question">

                    <div class="form-group">
                        <label for="q-title">Intitulé de la question (50 caractères max) :</label>
                        <input
                            type="text"
                            id="q-title"
                            name="title"
                            class="form-input"
                            placeholder="Ex : Quelle est votre fonctionnalité préférée ?"
                            maxlength="50"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="q-type">Type de question :</label>
                        <select id="q-type" name="type" class="form-select" onchange="toggleQuestionType(this.value)">
                            <option value="selection">Choix unique (QCM avec options)</option>
                            <option value="grade">Évaluation (Note de 1 à 5)</option>
                            <option value="freetext">Texte libre (Champ texte ouvert)</option>
                        </select>
                    </div>

                    <!-- Options pour le type Choix Unique -->
                    <div id="choices-container" style="margin-top: 16px;">
                        <label style="font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                            Options de réponse :
                        </label>
                        <div id="choices-list" class="choices-builder">
                            <div class="choice-input-row">
                                <input type="text" name="choices[]" class="form-input" placeholder="Option 1" maxlength="50" required>
                            </div>
                            <div class="choice-input-row">
                                <input type="text" name="choices[]" class="form-input" placeholder="Option 2" maxlength="50" required>
                            </div>
                        </div>
                        <div style="margin-top: 10px;">
                            <button type="button" class="button button-secondary button-sm" onclick="addChoiceField()">
                                + Ajouter une option
                            </button>
                        </div>
                    </div>

                    <!-- Note informative pour Grade -->
                    <div id="grade-info" style="display: none; background: #fffbeb; border: 1px solid #fde68a; padding: 12px 16px; border-radius: 6px; font-size: 14px; color: #b45309; margin-top: 14px;">
                        &#9733; Les participants pourront attribuer une note de <strong>1 à 5 étoiles</strong> avec un récapitulatif visuel.
                    </div>

                    <!-- Note informative pour FreeText -->
                    <div id="freetext-info" style="display: none; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 12px 16px; border-radius: 6px; font-size: 14px; color: #047857; margin-top: 14px;">
                        &#9998; Les participants disposeront d'un <strong>champ texte libre</strong> pour rédiger leur commentaire.
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
                        <button type="submit" class="button button-primary">
                            Ajouter la question
                        </button>
                    </div>
                </form>
            </section>

            <!-- Liste des questions existantes -->
            <section class="card" aria-labelledby="questions-list-title">
                <div class="card-title-row">
                    <h2 id="questions-list-title">Questions actuelles</h2>
                    <span class="badge-tag">
                        <?= count($this->questions) ?> question<?= count($this->questions) > 1 ? 's' : '' ?>
                    </span>
                </div>

                <?php if (empty($this->questions)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">&#10068;</div>
                        <p class="empty-title">Aucune question dans ce sondage</p>
                        <p class="empty-desc">Utilisez le formulaire ci-dessus pour ajouter votre première question !</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <?php foreach ($this->questions as $index => $question): ?>
                            <article class="question-card" style="margin-bottom: 0;">
                                <div class="question-header" style="margin-bottom: 0; padding-bottom: 0; border-bottom: 0;">
                                    <div class="question-title-area">
                                        <span class="question-order"><?= $index + 1 ?>.</span>
                                        <div>
                                            <h3 style="margin-bottom: 4px;"><?= htmlspecialchars($question->getTitle()) ?></h3>
                                            <span class="badge-tag <?= match($question->getType()) { 'selection' => 'badge-selection', 'grade' => 'badge-grade', 'freetext' => 'badge-freetext', default => '' } ?>">
                                                <?= $question->getTypeLabel() ?>
                                            </span>
                                        </div>
                                    </div>

                                    <form method="POST" action="/survey/edit?id=<?= $this->form->getId() ?>" onsubmit="return confirm('Supprimer définitivement cette question et les réponses associées ?');">
                                        <input type="hidden" name="action" value="delete_question">
                                        <input type="hidden" name="id_question" value="<?= $question->getId() ?>">
                                        <button type="submit" class="button-danger" title="Supprimer cette question">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>

                                <?php if ($question->isSelection() && !empty($question->getChoices())): ?>
                                    <div style="margin-top: 12px; padding-left: 28px;">
                                        <ul style="list-style: disc; color: #475569; font-size: 14px; display: flex; flex-direction: column; gap: 4px;">
                                            <?php foreach ($question->getChoices() as $choice): ?>
                                                <li><?= htmlspecialchars($choice->getTitle()) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>

        <script>
        function toggleQuestionType(type) {
            const choicesContainer = document.getElementById('choices-container');
            const gradeInfo = document.getElementById('grade-info');
            const freetextInfo = document.getElementById('freetext-info');
            const choiceInputs = choicesContainer.querySelectorAll('input[type="text"]');

            if (type === 'selection') {
                choicesContainer.style.display = 'block';
                gradeInfo.style.display = 'none';
                freetextInfo.style.display = 'none';
                choiceInputs.forEach(i => i.setAttribute('required', 'required'));
            } else if (type === 'grade') {
                choicesContainer.style.display = 'none';
                gradeInfo.style.display = 'block';
                freetextInfo.style.display = 'none';
                choiceInputs.forEach(i => i.removeAttribute('required'));
            } else if (type === 'freetext') {
                choicesContainer.style.display = 'none';
                gradeInfo.style.display = 'none';
                freetextInfo.style.display = 'block';
                choiceInputs.forEach(i => i.removeAttribute('required'));
            }
        }

        function addChoiceField() {
            const list = document.getElementById('choices-list');
            const count = list.children.length + 1;
            const row = document.createElement('div');
            row.className = 'choice-input-row';
            row.innerHTML = `
                <input type="text" name="choices[]" class="form-input" placeholder="Option ${count}" maxlength="50" required>
                <button type="button" class="button button-danger button-sm" onclick="this.parentElement.remove()">&#10005;</button>
            `;
            list.appendChild(row);
        }
        </script>
        <?php
    }
}
