<?php

namespace Views;

class ForgotPassword
{
    public function __construct(
            private ?string $token = null,
            private ?string $message = null,
            private ?string $error = null,
            private ?string $csrfToken = null,
    ) {
    }

    public function show(): void
    {
        begin_page('Mot de passe oublié', '/css/forgotPassword.css', noindex: True);
        ?>

        <main class="forgot-page">
            <section aria-labelledby="page-title">
                <header class="forgot-header">
                    <p class="page-descriptor">Accès au compte</p>
                    <h1 id="page-title">
                        <?= $this->token ? 'Nouveau mot de passe' : 'Mot de passe oublié' ?>
                    </h1>
                    <?php if (!$this->token && !$this->message): ?>
                        <p>Recevez un lien pour définir un nouveau mot de passe.</p>
                    <?php elseif ($this->token): ?>
                        <p>Choisissez un nouveau mot de passe sécurisé pour votre compte.</p>
                    <?php endif; ?>
                </header>

                <?php if ($this->error) { ?>
                    <p class="error" role="alert">
                        <?= htmlspecialchars($this->error) ?>
                    </p>
                <?php } ?>

                <?php if ($this->message) { ?>
                    <p class="success" role="status">
                        <?= htmlspecialchars($this->message) ?>
                    </p>

                    <p>
                        <a href="/login">Se connecter</a>
                    </p>
                <?php } elseif ($this->token) { ?>
                    <form class="forgot-form" method="post" action="/forgot">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <fieldset>
                            <legend>Modifier votre mot de passe</legend>

                            <input type="hidden" name="token" value="<?= htmlspecialchars($this->token) ?>">

                            <div class="field">
                                <label for="idpwd">Mot de passe</label>
                                <input type="password" id="idpwd" name="pwd" autocomplete="new-password" required>

                                <ul id="password-rules" class="password-rules">
                                    <li id="rule-length"><span>❌</span> Au moins 12 caractères</li>
                                    <li id="rule-upper"><span>❌</span> Une majuscule</li>
                                    <li id="rule-lower"><span>❌</span> Une minuscule</li>
                                    <li id="rule-number"><span>❌</span> Un chiffre</li>
                                    <li id="rule-special"><span>❌</span> Un caractère spécial</li>
                                </ul>
                            </div>

                            <div class="field">
                                <label for="idconf">Confirmez votre mot de passe</label>
                                <input type="password" id="idconf" name="conf" autocomplete="new-password" required>

                                <span id="conf-feedback" class="field-feedback" aria-live="polite"></span>
                            </div>

                            <button class="submit-button" type="submit" name="send" id="submit-btn" disabled>
                                Changer mon mot de passe
                            </button>
                        </fieldset>
                    </form>
                <?php } else { ?>
                    <form class="forgot-form" method="post" action="/forgot">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <fieldset>
                            <legend>Réinitialisation du mot de passe</legend>

                            <div class="field">
                                <label for="email">Adresse e-mail</label>
                                <input type="email" name="email" id="email" required autocomplete="email">
                            </div>

                            <button class="submit-button" type="submit">Envoyer le lien</button>
                        </fieldset>
                    </form>
                <?php } ?>
            </section>
        </main>

        <script src="/js/formStateForgot.js"></script>
        <script src="/js/validPassword.js"></script>

        <?php
        end_page();
    }
}