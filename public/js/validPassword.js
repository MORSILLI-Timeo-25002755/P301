document.addEventListener("DOMContentLoaded", function() {

    const pwdInput = document.getElementById('idpwd');
    const confInput = document.getElementById('idconf');
    const confFeedback = document.getElementById('conf-feedback');

    if (pwdInput && confInput) {

        pwdInput.addEventListener('input', function(e) {
            const pwd = e.target.value;

            const isLength = pwd.length >= 12;
            const isUpper = /[A-Z]/.test(pwd);
            const isLower = /[a-z]/.test(pwd);
            const isNum = /[0-9]/.test(pwd);
            const isSpec = /[\W_]/.test(pwd);

            updateRule('rule-length', isLength);
            updateRule('rule-upper', isUpper);
            updateRule('rule-lower', isLower);
            updateRule('rule-number', isNum);
            updateRule('rule-special', isSpec);

            window.formState.password = (isLength && isUpper && isLower && isNum && isSpec);

            window.formState.confirm = (confInput.value === pwd && pwd !== '');

            window.checkFormValidity();
        });

        confInput.addEventListener('input', function(e) {
            const conf = e.target.value;
            const pwd = pwdInput.value;

            window.formState.confirm = (conf === pwd && conf !== '');
            window.checkFormValidity();

            confInput.style.borderColor = window.formState.confirm ? 'green' : 'red';
        });
    }

    function checkPasswordMatch() {
        const pwd = pwdInput.value;
        const conf = confInput.value;

        // Mise à jour de l'état global
        window.formState.confirm = (conf === pwd && conf !== '');
        window.checkFormValidity();

        // Gestion du texte visuel
        if (conf === '') {
            // Si le champ est vide, on efface tout
            confFeedback.textContent = '';
            confInput.style.borderColor = '';
        } else if (conf === pwd) {
            // Succès
            confFeedback.textContent = '✅ Les mots de passe correspondent';
            confFeedback.style.color = 'green';
            confInput.style.borderColor = 'green';
        } else {
            // Erreur
            confFeedback.textContent = '❌ Les mots de passe ne correspondent pas';
            confFeedback.style.color = 'red';
            confInput.style.borderColor = 'red';
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

        updateRule('rule-length', isLength);
        updateRule('rule-upper', isUpper);
        updateRule('rule-lower', isLower);
        updateRule('rule-number', isNum);
        updateRule('rule-special', isSpec);

        window.formState.password = (isLength && isUpper && isLower && isNum && isSpec);

        // On vérifie si ça correspond (utile si l'utilisateur modifie le mot de passe original après avoir tapé la confirmation)
        checkPasswordMatch();
    });

    // Écouteur sur le champ Confirmation
    confInput.addEventListener('input', function() {
        checkPasswordMatch();
    });

    function updateRule(elementId, isValid) {
        const liElement = document.getElementById(elementId);
        if (!liElement) return;

        const spanIcon = liElement.querySelector('span'); // On cible juste le <span>

        if (isValid) {
            spanIcon.textContent = '✅';
            liElement.style.color = 'green';
        } else {
            spanIcon.textContent = '❌';
            liElement.style.color = 'red';
        }
    }
});