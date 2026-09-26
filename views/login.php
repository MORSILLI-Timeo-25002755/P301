<?php

namespace Views;

class Login
{
    public function __construct(private ?string $error = null) {}

    public function show(): void
    {
        ?>
        <?php if ($this->error) { ?>
            <p style="color:red;"><?= htmlspecialchars($this->error) ?></p>
        <?php }
        begin_page('Login','_assets/css/login.css'); ?>
        <form method="post" action="/login">
            <input type="email" name="email" required>
            <br>
            <input type="password" name="password" required>
            <br>
            <button type="submit">Se connecter</button>
        </form>
        <?php
    }
}