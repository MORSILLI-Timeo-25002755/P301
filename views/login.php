<?php

namespace Views;

class Login
{
    public function show(): void { // PSR-12: opening brace next line
        begin_page('Login','_assets/css/login.css');
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