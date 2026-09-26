<?php

namespace Views;

class Register
{
    public function show(bool $notFilled, bool $validPassword): void
    {
        begin_page('Register', '_assets/css/register.css');
        ?>
        <form method="POST" action="/register">
            <fieldset>
                <legend>Inscription</legend>

                <p>Créez votre compte</p>

                <label for="idemail">E-mail</label>
                <input type="email" id="idemail" name="email" autocomplete="off" required>

                <label for="idusrn">Nom d'utilisateur</label>
                <input type="text" id="idusrn" name="username" autocomplete="off" required>

                <label for="idpwd">Mot de passe</label>
                <input type="password" id="idpwd" name="pwd" autocomplete="off" required>

                <label for="idconf">Confirmez votre mot de passe</label>
                <input type="password" id="idconf" name="conf" autocomplete="off" required>

                <?php if ($notFilled): ?>
                    <p class="error">Veuillez remplir tous les champs !</p>
                <?php endif; ?>

                <?php if (!$validPassword): ?>
                    <p class="error">Échec de la confirmation du mot de passe.</p>
                <?php endif; ?>

                <input type="submit" name="send" value="Valider">
            </fieldset>
        </form>
        <?php
    }
}