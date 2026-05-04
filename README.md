# StudyFlow - Application de Gestion d'Études

## Vue d'ensemble
StudyFlow est une application web PHP pour gérer les tâches d'études, examens, cours et vacances. Elle permet aux étudiants de s'organiser efficacement avec un système de notifications et de suivi des deadlines.

## Technologies utilisées

### Backend : PHP avec PDO
- **Pourquoi PHP ?** Langage serveur populaire, facile à déployer sur XAMPP/WAMP, grande communauté
- **Pourquoi PDO ?** Sécurité contre les injections SQL, abstraction de base de données, requêtes préparées
- **Où ?** Tous les fichiers `.php` (pages et API)+

### Base de données : MySQL/MariaDB
- **Pourquoi MySQL/MariaDB ?** Compatible avec XAMPP, performant pour les applications web, support des relations
- **Pourquoi PDO MySQL ?** Pilote standard, fonctionne sur MySQL et MariaDB
- **Où ?** `includes/config.php` (connexion), `database.sql` (schéma)

### Frontend : HTML/CSS/JavaScript
- **Pourquoi HTML/CSS ?** Standard web, responsive design, pas de framework lourd nécessaire
- **Pourquoi JavaScript vanilla ?** Pas de dépendances externes, léger, suffisant pour l'interactivité
- **Où ?** `index.html` (page d'accueil), `style.css` (styles), `js/script.js` (interactivité)

## Structure du projet

### Pages principales (`/` - racine)
- `index.html` : Page d'accueil avec navigation
- `login.php` / `register.php` : Authentification
- `dashboard.php` : Tableau de bord principal
- `tasks.php` : Gestion des tâches
- `exams.php` : Gestion des examens
- `classes.php` : Gestion des cours
- `calendar.php` : Calendrier intégré
- `vacations.php` : Gestion des vacances
- `focus-timer.php` : Minuteur Pomodoro

### API (`api/`)
- `tasks.php` : CRUD pour les tâches (GET, POST, PUT, DELETE)
- `update-task-status.php` : Mise à jour du statut des tâches
- `delete-task.php` : Suppression de tâches
- *(Autres fichiers similaires pour exams, classes, vacations)*

### Configuration (`includes/`)
- `config.php` : Connexion DB, fonctions utilitaires, sessions

### Assets
- `js/script.js` : JavaScript pour l'interactivité (AJAX, modales, notifications)
- `style.css` : Styles CSS responsives

### Base de données
- `database.sql` : Schéma avec tables `users`, `tasks`, `exams`, `classes`, `vacations`

## Fonctionnalités clés

### Gestion des tâches
- Ajout/édition/suppression avec dates d'échéance
- Statuts : pending → in_progress → done
- Notifications de deadline (toast system)
- Filtrage et statistiques

### Sécurité
- Sessions PHP pour l'authentification
- PDO avec requêtes préparées
- Validation des entrées
- Protection CSRF basique

### Interface utilisateur
- Design responsive (mobile-friendly)
- Toast notifications pour feedback
- Modales pour édition
- Skeleton loaders pour chargement

## Installation

1. Importer `database.sql` dans MySQL/MariaDB
2. Configurer `includes/config.php` avec vos credentials DB
3. Placer dans `htdocs/` de XAMPP
4. Accéder via `http://localhost/studyflow`

## Choix techniques expliqués

- **Pas de framework PHP** : Projet simple, pas besoin de complexité (Laravel/Symfony)
- **Pas de framework JS** : Vanilla JS suffit, évite les dépendances
- **Base de données relationnelle** : Relations entre users/tasks nécessaires
- **Architecture API** : Séparation frontend/backend, AJAX pour fluidité
- **Sessions PHP** : Simple et sécurisé pour l'authentification

## Améliorations possibles
- Migration vers un framework (Laravel, React)
- Ajout de tests unitaires
- API REST complète avec JWT
- Déploiement cloud (Heroku, Vercel)</content>
<parameter name="filePath">c:\xampp\htdocs\studyflow\README.md