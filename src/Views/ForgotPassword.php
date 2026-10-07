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

        <main>
            <section aria-labelledby="page-title">
                <h1 id="page-title">
                    <?= $this->token ? 'Nouveau mot de passe' : 'Mot de passe oublié' ?>
                </h1>

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

                    <form method="post" action="/forgot">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <fieldset>
                            <legend>Modifier votre mot de passe</legend>

                            <input
                                    type="hidden"
                                    name="token"
                                    value="<?= htmlspecialchars($this->token) ?>"
                            >

                            <label for="idpwd">Mot de passe</label>
                            <input type="password" id="idpwd" name="pwd" autocomplete="off" required>

                            <ul id="password-rules" style="list-style-type: none; padding-left: 0;">
                                <li id="rule-length"><span>❌</span> Au moins 12 caractères</li>
                                <li id="rule-upper"><span>❌</span> Une majuscule</li>
                                <li id="rule-lower"><span>❌</span> Une minuscule</li>
                                <li id="rule-number"><span>❌</span> Un chiffre</li>
                                <li id="rule-special"><span>❌</span> Un caractère spécial</li>
                            </ul>

                            <label for="idconf">Confirmez votre mot de passe</label>
                            <input type="password" id="idconf" name="conf" autocomplete="off" required>

                            <span id="conf-feedback" style="display: block; margin-bottom: 15px; font-size: 0.9em;"></span>

                            <button type="submit" name="send" id="submit-btn" disabled style="opacity: 0.5; cursor: not-allowed;">Changer mon mot de passe</button>


                        </fieldset>
                    </form>

                <?php } else { ?>

                    <form method="post" action="/forgot">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <fieldset>
                            <legend>Réinitialisation du mot de passe</legend>

                            <p>
                                <label for="email">
                                    Votre adresse e-mail :
                                </label>
                                <input
                                        type="email"
                                        name="email"
                                        id="email"
                                        required
                                        autocomplete="email"
                                >
                            </p>

                            <p>
                                <button type="submit">
                                    Envoyer le lien
                                </button>
                            </p>
                        </fieldset>
                    </form>

                <?php } ?>
            </section>
        </main>
        <script src="/js/formStateForgot.js"></script>
        <script src="/js/validPassword.js"></script>

        <?php end_page();
    }
}