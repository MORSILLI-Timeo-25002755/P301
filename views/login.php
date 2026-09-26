<?php

namespace Views;

class Login
{
    public function show(): void { // PSR-12: opening brace next line
        begin_page('Login', '_assets/css/login.css');
        ?>
        <form method="post" action="">
            <h1>Connexion</h1>
            <p>Connectez-vous à votre compte</p>

            <label for="email">Adresse e-mail</label>
            <input type="email" name="email" required>

            <label for="pwd">Mot de passe</label>
            <input type="password" name="password" required>

            <button type="submit">Se connecter</button>
        </form>
        <?php
    }
}