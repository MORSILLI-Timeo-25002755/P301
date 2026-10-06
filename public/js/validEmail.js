document.addEventListener("DOMContentLoaded", function() {

    // 1. On récupère les éléments une seule fois au chargement
    const emailInput = document.getElementById('idemail');
    const feedback = document.getElementById('email-feedback');
    const csrfTokenInput = document.querySelector('input[name="csrf_token"]');

    // On vérifie que tous les éléments existent sur la page
    if (emailInput && feedback && csrfTokenInput) {

        emailInput.addEventListener('blur', async function(e) {
            const email = e.target.value.trim(); // .trim() enlève les espaces en trop

            // Si le champ est vide, on nettoie le texte et on arrête
            if (email.length === 0) {
                feedback.textContent = '';
                return;
            }

            // Vérification locale rapide (Format d'email basique)
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                feedback.textContent = '❌ Format d\'email invalide';
                feedback.style.color = 'red';
                return;
            }

            feedback.textContent = '⏳ Vérification...';
            feedback.style.color = 'orange';

            try {
                // Création propre des paramètres POST
                const params = new URLSearchParams();
                params.append('email', email);
                params.append('csrf_token', csrfTokenInput.value);

                const response = await fetch('/api/check-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: params.toString()
                });

                // On vérifie que le serveur a répondu avec un code 200 OK
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }

                const data = await response.json();

                // Gestion des différentes réponses de votre PHP
                if (data.error) {
                    feedback.textContent = '❌ ' + data.error;
                    feedback.style.color = 'red';
                    return;
                }

                if (data.available) {
                    feedback.textContent = '✅ Email disponible';
                    feedback.style.color = 'green';
                    window.formState.email = true;
                } else {
                    feedback.textContent = '❌ Cet email est déjà utilisé';
                    feedback.style.color = 'red';
                    window.formState.email = false;
                }
                window.checkFormValidity();

            } catch (error) {
                console.error("Erreur lors de la vérification de l'email :", error);
                feedback.textContent = '❌ Erreur de connexion au serveur';
                feedback.style.color = 'red';
            }
        });
    }
});