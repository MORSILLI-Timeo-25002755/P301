<?php

namespace Views;

class forgotPassword
{
    public function __construct(
        private ?string $token = null,
        private ?string $message = null,
        private ?string $error = null
    ) {}

    public function show(): void
    {
        begin_page('Mot de passe oublié', '_assets/css/forgotPassword.css');
        ?><h1><?= $this->token ? 'Nouveau mot de passe' : 'Mot de passe oublié' ?></h1>

        <?php if ($this->error) { ?>
        <p style="color:red;"><?= htmlspecialchars($this->error) ?></p>
    <?php } ?>

        <?php if ($this->message) { ?>
        <p style="color:green;"><?= htmlspecialchars($this->message) ?></p>
        <p><a href="/login">Se connecter</a></p>

    <?php } elseif ($this->token) { ?>
        <form method="post" action="/forgot">
            <input type="hidden" name="token" value="<?= htmlspecialchars($this->token) ?>">

            <label for="password">Nouveau mot de passe :</label>
            <input type="password" name="password" id="password" minlength="8" required>

            <label for="confirm">Confirmer :</label>
            <input type="password" name="confirm" id="confirm" minlength="8" required>

            <button type="submit">Changer le mot de passe</button>
        </form>

    <?php } else { ?>
        <form method="post" action="/forgot">
            <label for="email">Votre adresse email :</label>
            <input type="email" name="email" id="email" required>
            <button type="submit">Envoyer le lien</button>
        </form>
        <?php
    }
    }
}