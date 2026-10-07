<?php

namespace Views;

use \Models\Users;

readonly class Profile
{
    public function __construct(
        private Users $user,
        private string $csrfToken,
        private string $message = '',
        private bool $success = false
    ) {}

    public function show(): void
    {
        begin_page('Mon profil', '/css/profile.css', noindex: true);
        ?>
        <main class="profile-page">
            <header class="profile-header">
                <div class="page-descriptor">Compte utilisateur</div>
                <h1>Mon profil</h1>
                <p>Consultez et modifiez vos informations personnelles.</p>
            </header>

            <?php if ($this->message !== ''): ?>
                <p class="profile-message <?= $this->success ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($this->message, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <section class="profile-card">
                <h2>Informations personnelles</h2>
                <form method="post" action="/profile">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="update">

                    <label for="username">Nom d’utilisateur</label>
                    <input id="username" name="username" type="text" value="<?= htmlspecialchars($this->user->getUsername() ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="50" required>

                    <label for="email">Adresse e-mail</label>
                    <input id="email" name="email" type="email" value="<?= htmlspecialchars($this->user->getEmail(), ENT_QUOTES, 'UTF-8') ?>" maxlength="50" required>

                    <div class="password-heading">
                        <h2>Changer le mot de passe</h2>
                        <p>Laissez ces champs vides pour conserver votre mot de passe actuel.</p>
                    </div>

                    <label for="password">Nouveau mot de passe</label>
                    <input id="password" name="password" type="password" minlength="12" autocomplete="new-password">

                    <label for="password_confirmation">Confirmation du mot de passe</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" minlength="12" autocomplete="new-password">

                    <button class="button-primary" type="submit">Enregistrer les modifications</button>
                </form>
            </section>

            <section class="danger-card">
                <h2>Supprimer mon compte</h2>
                <p>Cette action est définitive. Vos sondages, réponses et informations personnelles seront supprimés.</p>
                <form method="post" action="/profile" onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ? Cette action est irréversible.');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="delete">
                    <button class="button-danger" type="submit">Supprimer définitivement mon compte</button>
                </form>
            </section>
        </main>
        <?php
        end_page();
    }
}
