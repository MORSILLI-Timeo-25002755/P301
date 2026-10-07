document.addEventListener("DOMContentLoaded", function() {

    const pwdInput = document.getElementById('idpwd');
    const confInput = document.getElementById('idconf');
    const confFeedback = document.getElementById('conf-feedback');

    // Si on n'est pas sur une page avec ces champs, on arrête le script ici
    if (!pwdInput || !confInput) return;

    function updateRule(elementId, isValid) {
        const liElement = document.getElementById(elementId);
        if (!liElement) return;

        const spanIcon = liElement.querySelector('span');

        if (isValid) {
            spanIcon.textContent = '✅';
            liElement.style.color = 'green';
        } else {
            spanIcon.textContent = '❌';
            liElement.style.color = 'red';
        }
    }

    function checkPasswordMatch() {
        const pwd = pwdInput.value;
        const conf = confInput.value;
        const isMatch = (conf === pwd && conf !== '');

        // 💡 L'ASTUCE EST ICI : On met à jour formState SEULEMENT s'il existe sur la page
        if (typeof window.formState !== 'undefined') {
            window.formState.confirm = isMatch;
        }

        // On met à jour formStateForgot SEULEMENT s'il existe sur la page
        if (typeof window.formStateForgot !== 'undefined') {
            window.formStateForgot.confirm = isMatch;
        }

        // Gestion visuelle
        if (confFeedback) {
            if (conf === '') {
                confFeedback.textContent = '';
                confInput.style.borderColor = '';
            } else if (isMatch) {
                confFeedback.textContent = '✅ Les mots de passe correspondent';
                confFeedback.style.color = 'green';
                confInput.style.borderColor = 'green';
            } else {
                confFeedback.textContent = '❌ Les mots de passe ne correspondent pas';
                confFeedback.style.color = 'red';
                confInput.style.borderColor = 'red';
            }
        }

        // On appelle la fonction de vérification du bouton si elle a été déclarée
        if (typeof window.checkFormValidity === 'function') {
            window.checkFormValidity();
        }
    }

    // Écouteur sur le champ Mot de passe principal
    pwdInput.addEventListener('input', function(e) {
        const pwd = e.target.value;

        const isLength = pwd.length >= 12;
        const isUpper = /[A-Z]/.test(pwd);
        const isLower = /[a-z]/.test(pwd);
        const isNum = /[0-9]/.test(pwd);
        const isSpec = /[\W_]/.test(pwd);
        const isPasswordValid = (isLength && isUpper && isLower && isNum && isSpec);

        updateRule('rule-length', isLength);
        updateRule('rule-upper', isUpper);
        updateRule('rule-lower', isLower);
        updateRule('rule-number', isNum);
        updateRule('rule-special', isSpec);

        // 💡 PAREIL ICI : Mise à jour sécurisée des états
        if (typeof window.formState !== 'undefined') {
            window.formState.password = isPasswordValid;
        }
        if (typeof window.formStateForgot !== 'undefined') {
            window.formStateForgot.password = isPasswordValid;
        }

        checkPasswordMatch();
    });

    // Écouteur sur le champ de confirmation
    confInput.addEventListener('input', checkPasswordMatch);
});