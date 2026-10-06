<?php

namespace Views;
class Register
{
    public function __construct(private String $csrfToken) {}
    public function show(bool $notFilled, bool $validPassword, String $reCaptchaError = ''): void
    {
        begin_page('Register', '/css/register.css', noindex: True);
        ?>
        <form method="POST" action="/register">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <fieldset>
                <legend>Inscription</legend>

                <p>Créez votre compte</p>

                <label for="idemail">E-mail</label>
                <input type="email" id="idemail" name="email" autocomplete="off" required>

                <label for="idusrn">Nom d'utilisateur</label>
                <input type="text" id="idusrn" name="username" autocomplete="off" required>
                <span id="username-feedback"></span>

                <label for="idpwd">Mot de passe</label>
                <input type="password" id="idpwd" name="pwd" autocomplete="off" required>

                <ul id="password-rules" style="list-style-type: none; padding-left: 0;">
                    <li id="rule-length"><span>❌</span> Au moins 12 caractères</li>
                    <li id="rule-upper"><span>❌</span> Une majuscule</li>
                    <li id="rule-lower"><span>❌</span> Une minuscule</li>
                    <li id="rule-number"><span>❌</span> Un chiffre</li>
                    <li id="rule-special"><span>❌</span> Un caractère spécial</li>
                </ul>

                <label for="idconf">Confirmez votre mot de passe</label>
                <input type="password" id="idconf" name="conf" autocomplete="off" required>

                <span id="conf-feedback" style="display: block; margin-bottom: 15px; font-size: 0.9em;"></span>

                <!-- /<?php if ($notFilled): ?>
                    <p class="error">Veuillez remplir tous les champs !</p>
                <?php endif; ?>

                <?php if (!$validPassword): ?>
                    <p class="error">Échec de la confirmation du mot de passe.</p>
                <?php endif; ?> -->

                <section class="g-recaptcha" data-sitekey="6LfIy-EtAAAAAIYNRjyC47m6Fv0Y0mazw94Inkvl" style="margin-bottom: 15px;"></section>
                <?php if (!empty($recaptchaError)): ?>
                    <p class="error"><?= htmlspecialchars($reCaptchaError, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <input type="submit" name="send" id="submit-btn" value="Valider" disabled style="opacity: 0.5; cursor: not-allowed;">
            </fieldset>
        </form>

        <script src="/js/formState.js"></script>
        <script src="/js/validUsername.js"></script>
        <script src="/js/validPassword.js"></script>
        <script src="https://www.google.com/recaptcha/api.js"></script>
        <?php         end_page();
    }
}