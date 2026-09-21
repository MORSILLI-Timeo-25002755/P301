<?php

namespace Views;

class LoginView
{
    public function show(): void { // PSR-12: opening brace next line
        ?>
        <form method="post" action="">
            <input type="email" name="email" required>
            <br>
            <input type="password" name="pwd" required>
            <br>
            <button type="submit">Se connecter</button>
        </form>
        <?php
    }
}