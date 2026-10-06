document.addEventListener("DOMContentLoaded", function() {

    // 1. On récupère les éléments une seule fois au chargement
    const usernameInput = document.getElementById('idusrn');
    const feedback = document.getElementById('username-feedback');
    const csrfTokenInput = document.querySelector('input[name="csrf_token"]');

    // On vérifie que tous les éléments existent sur la page
    if (usernameInput && feedback && csrfTokenInput) {

        usernameInput.addEventListener('blur', async function(e) {
            const username = e.target.value.trim();

            if (username.length === 0) {
                feedback.textContent = '';
                return;
            }


            feedback.textContent = '⏳ Vérification...';
            feedback.style.color = 'orange';

            try {
                const params = new URLSearchParams();
                params.append('username', username);
                params.append('csrf_token', csrfTokenInput.value);

                const response = await fetch('/api/check-username', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: params.toString()
                });

                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }

                const data = await response.json();

                if (data.error) {
                    feedback.textContent = '❌ ' + data.error;
                    feedback.style.color = 'red';
                    return;
                }

                if (data.available) {
                    feedback.textContent = '✅ username disponible';
                    feedback.style.color = 'green';
                    window.formState.username = true;
                } else {
                    feedback.textContent = '❌ Cet username est déjà utilisé';
                    feedback.style.color = 'red';
                    window.formState.username = false;
                }
                window.checkFormValidity();

            } catch (error) {
                console.error("Erreur lors de la vérification de l'username :", error);
                feedback.textContent = '❌ Erreur de connexion au serveur';
                feedback.style.color = 'red';
            }
        });
    }
});