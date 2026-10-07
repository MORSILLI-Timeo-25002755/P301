# HorseForm

HorseForm est une application web de création et de partage de sondages. Elle permet aux utilisateurs de créer des formulaires, d'y ajouter des questions, de consulter les réponses et de gérer leur compte.

## Fonctionnalités

- création de compte avec validation reCAPTCHA ;
- connexion sécurisée et récupération du mot de passe par e-mail ;
- connexion automatique après l'inscription ;
- tableau de bord utilisateur ;
- consultation des sondages disponibles ;
- gestion du profil : modification de l'e-mail, du nom d'utilisateur et du mot de passe ;
- suppression du compte et des données associées ;
- plan du site ;
- protection CSRF, sessions sécurisées et requêtes SQL préparées.

## Technologies

- PHP ;
- MySQL ;
- PDO ;
- HTML/CSS ;
- Composer ;
- PHPMailer pour l'envoi d'e-mails ;
- phpdotenv pour la configuration par variables d'environnement.

## Prérequis

- PHP 8 ou une version compatible avec les dépendances du projet ;
- Composer ;
- MySQL ;
- les extensions PHP `PDO`, `cURL` et `Couchbase` déclarées dans `composer.json`.

## Installation

1. Cloner le dépôt et se placer dans son dossier :

   ```bash
   git clone <URL_DU_DEPOT>
   cd P301
   ```

2. Installer les dépendances :

   ```bash
   composer install
   ```

3. Créer la base de données MySQL et exécuter le script SQL du projet :

   ```bash
   mysql -u <utilisateur> -p <nom_de_la_base> < bdd.sql
   ```

4. Créer un fichier `.env` à la racine du projet. Ne jamais publier ce fichier :

   ```dotenv
   DB_HOST=
   DB_USER=
   DB_PASSWORD=
   DB_DBNAME=

   MAIL_ADDRESS=
   MAIL_PASSWORD=
   MAIL_HOST=
   SMTP_PORT=465

   APP_URL=http://localhost:8080
   RECAPTCHA_SECRET_KEY=
   ```

   Les identifiants de base de données, de messagerie et la clé reCAPTCHA doivent rester privés.

## Lancer le projet en local

Depuis la racine du dépôt :

```bash
php -S localhost:8080 -t public
```

Puis ouvrir [http://localhost:8080](http://localhost:8080).

Le point d'entrée de l'application est `public/index.php`. Le serveur doit utiliser `public` comme document root afin que les fichiers internes du projet ne soient pas exposés directement.

## Routes principales

| Route | Description | Accès |
| --- | --- | --- |
| `/` | Accueil | Public |
| `/login` | Connexion | Public |
| `/register` | Inscription | Public |
| `/forgot` | Mot de passe oublié | Public |
| `/surveys` | Liste de tous les sondages | Public |
| `/sitemap` | Plan du site | Public |
| `/dashboard` | Tableau de bord | Utilisateur connecté |
| `/profile` | Gestion du profil | Utilisateur connecté |
| `/logout` | Déconnexion | Utilisateur connecté |
| `/api/check-username` | Vérification de disponibilité d'un nom d'utilisateur | API |

## Organisation du projet

```text
P301/
├── public/                  # Point d'entrée et ressources publiques
│   ├── index.php            # Routeur principal
│   ├── css/                 # Feuilles de style
│   └── js/                  # Scripts côté navigateur
├── src/
│   ├── Controllers/         # Contrôleurs des routes
│   ├── Models/              # Entités et repositories
│   ├── Views/               # Vues PHP
│   └── _assets/Includes/    # Connexion BDD, autoload et gestion de session
├── bdd.sql                  # Structure de la base de données
├── composer.json             # Dépendances PHP
└── .env                      # Configuration locale, non versionnée
```

## Base de données

Le schéma repose notamment sur les tables suivantes :

- `Users` : comptes utilisateurs ;
- `Form` : sondages ;
- `Question` : questions des sondages ;
- `Selection`, `Grade` et `FreeText` : types de questions ;
- `Choice` : choix des questions à sélection ;
- `Answer` et les tables `Ans_*` : réponses des utilisateurs.

Les clés étrangères relient les sondages à leurs créateurs, les questions à leurs sondages et les réponses aux utilisateurs et aux questions.

## Sécurité

Le projet utilise notamment :

- `password_hash()` et `password_verify()` pour les mots de passe ;
- des requêtes préparées avec PDO ;
- des jetons CSRF pour les formulaires sensibles ;
- la régénération de l'identifiant de session après connexion et inscription ;
- des cookies de session `HttpOnly` et `SameSite=Strict` ;
- des en-têtes HTTP de protection contre le clickjacking et le MIME sniffing ;
- l'échappement HTML des données affichées ;
- la conservation de l'adresse e-mail saisie en cas d'échec de connexion, sans conserver le mot de passe.

## Déploiement

Le workflow GitHub Actions `.github/workflows/deploy.yml` déploie automatiquement la branche `main` sur le serveur configuré lorsque cette branche reçoit un push.

Les secrets suivants doivent être configurés dans les secrets du dépôt :

- `SERVER_HOST` ;
- `SERVER_USER` ;
- `SERVER_SSH_KEY`.

La configuration applicative et les identifiants de production doivent être définis directement dans l'environnement du serveur et ne doivent pas être ajoutés au dépôt.

## Équipe

Projet réalisé par :

- Lohann BALBAS ;
- Gaël BARTHELEMY ;
- Timéo MORSILLI ;
- Mattéo YANNI.

## Utilisation de l'IA

GitHub Copilot a été utilisé comme assistant de développement afin d'aller plus vite sur certaines tâches chronophages, notamment l'écriture de code répétitif, la mise en forme et la documentation. Les choix techniques, l'intégration et la validation du projet restent réalisés par l'équipe.
