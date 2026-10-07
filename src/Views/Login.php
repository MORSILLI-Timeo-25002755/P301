<?php

namespace Views;
class Login
{

    public function __construct(
        private string $csrfToken,
        private string $email = '',
        private string $error = ''
    ) {}

    public function show(): void { // PSR-12: opening brace next line
        begin_page('Login', '/css/login.css', noindex: True);
        ?>
        <form method="post" action="/login">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <h1>Connexion</h1>
            <p>Connectez-vous à votre compte</p>

            <?php if ($this->error !== ''): ?>
                <p class="login-error"><?= htmlspecialchars($this->error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <label for="email">Adresse e-mail</label>
            <input type="email" name="email" value="<?= htmlspecialchars($this->email, ENT_QUOTES, 'UTF-8') ?>" required>

            <label for="pwd">Mot de passe</label>
            <input type="password" name="password" required>

            <button type="submit">Se connecter</button>
            <a href="/forgot" class="forgot-link">Mot de passe oublié ?</a>
        </form>
        <?php end_page();
    }
}