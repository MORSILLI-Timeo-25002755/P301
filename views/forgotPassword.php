<?php

namespace Views;

class ForgotPassword
{
    public function __construct(
            private ?string $token = null,
            private ?string $message = null,
            private ?string $error = null
    ) {}

    public function show(): void
    {
        begin_page('Mot de passe oublié', '_assets/css/forgotPassword.css');
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
                        <fieldset>
                            <legend>Modifier votre mot de passe</legend>

                            <input
                                    type="hidden"
                                    name="token"
                                    value="<?= htmlspecialchars($this->token) ?>"
                            >

                            <p>
                                <label for="password">
                                    Nouveau mot de passe :
                                </label>
                                <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        minlength="8"
                                        required
                                        autocomplete="new-password"
                                >
                            </p>

                            <p>
                                <label for="confirm">
                                    Confirmer le mot de passe :
                                </label>
                                <input
                                        type="password"
                                        name="confirm"
                                        id="confirm"
                                        minlength="8"
                                        required
                                        autocomplete="new-password"
                                >
                            </p>

                            <button type="submit">
                                Changer le mot de passe
                            </button>
                        </fieldset>
                    </form>

                <?php } else { ?>

                    <form method="post" action="/forgot">
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

        <?php
    }
}