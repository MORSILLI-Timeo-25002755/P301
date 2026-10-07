<?php

namespace Views;
class Register
{
    public function __construct(private String $csrfToken) {}
    public function show(String $reCaptchaError = ''): void
    {
        begin_page('Register', '/css/register.css', noindex: True);
        ?>
        <form class="register-form" method="POST" action="/register">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <fieldset>
                <legend>Créer votre compte</legend>
                <p class="form-intro">Rejoignez HorseForm pour créer et partager vos sondages.</p>

                <div class="field">
                    <label for="idemail">Adresse e-mail</label>
                    <input type="email" id="idemail" name="email" autocomplete="email" required>
                </div>

                <div class="field">
                    <label for="idusrn">Nom d'utilisateur</label>
                    <input type="text" id="idusrn" name="username" autocomplete="username" required>
                    <span id="username-feedback" class="field-feedback" aria-live="polite"></span>
                </div>

                <div class="field">
                    <label for="idpwd">Mot de passe</label>
                    <input type="password" id="idpwd" name="pwd" autocomplete="new-password" required>

                    <ul id="password-rules" class="password-rules">
                        <li id="rule-length"><span>❌</span> Au moins 12 caractères</li>
                        <li id="rule-upper"><span>❌</span> Une majuscule</li>
                        <li id="rule-lower"><span>❌</span> Une minuscule</li>
                        <li id="rule-number"><span>❌</span> Un chiffre</li>
                        <li id="rule-special"><span>❌</span> Un caractère spécial</li>
                    </ul>
                </div>

                <div class="field">
                    <label for="idconf">Confirmez votre mot de passe</label>
                    <input type="password" id="idconf" name="conf" autocomplete="new-password" required>

                    <span id="conf-feedback" class="field-feedback" aria-live="polite"></span>
                </div>

                <section class="captcha-wrapper" aria-label="Vérification anti-robot">
                    <div class="g-recaptcha" data-sitekey="6LfIy-EtAAAAAIYNRjyC47m6Fv0Y0mazw94Inkvl"></div>
                </section>
                <?php if (!empty($recaptchaError)): ?>
                    <p class="error" role="alert"><?= htmlspecialchars($reCaptchaError, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <input type="submit" name="send" id="submit-btn" value="Créer mon compte" disabled>
                <p class="login-prompt">Vous avez déjà un compte ? <a href="/login">Se connecter</a></p>
            </fieldset>
        </form>

        <script src="/js/formState.js"></script>
        <script src="/js/validUsername.js"></script>
        <script src="/js/validPassword.js"></script>
        <script src="https://www.google.com/recaptcha/api.js"></script>
        <?php         end_page();
    }
}