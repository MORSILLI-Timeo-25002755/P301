<?php

namespace Views;

class Register {
    public function show(bool $notFilled, bool $validPassword): void {
        ?>
        <main>
            <form method="POST" action="/register">
                <fieldset>

                    <legend>Inscription</legend>

                    <label for="idemail">E-mail :</label>
                    <input type="email" id="idemail" name="email" autocomplete="off"/><br>

                    <label for="idusrn">Nom d'utilisateur :</label>
                    <input type="text" id="idusrn" name="username" autocomplete="off"/><br>

                    <label for="idpwd">Mot de passe :</label>
                    <input type="password" id="idpwd" name="pwd" autocomplete="off"/><br>

                    <label for="idconf">Confirmez votre mot de passe :</label>
                    <input type="password" id="idconf" name="conf" autocomplete="off"/><br>

                    <?php if($notFilled): ?>
                        <p>Veuillez remplir tous les champs !</p>
                    <?php endif; ?>
                    <?php if(!$validPassword): ?>
                        <p>Echec de la confirmation du mot de passe</p>
                    <?php endif; ?>

                    <input type="submit" name="send" value="Valider"/>

                </fieldset>
            </form>
        </main>
        <?php
    }
}