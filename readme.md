# Rent a Vélo — TP location de vélos (POO, MVC, Singleton, AJAX)

## Installation (MAMP)

1. Vérifier les identifiants dans `src/Models/config.php` et démarrer MySQL dans MAMP.
2. À la racine du projet : `php scripts/init-database.php` (Windows : `php scripts\init-database.php`). Cette commande crée `rent_a_velo` avec les données de démonstration uniquement si elle est absente ; elle refuse d'écraser une base existante. Le script SQL brut contient un `DROP DATABASE` : ne pas le réimporter sur une base contenant des données à conserver.
3. À la racine du projet : `composer dump-autoload`.
4. Ouvrir `http://localhost:81/B2/MVC/tp%20rent%20a%20velo/public/` (adapter le port).
   Pour ce dossier : `http://localhost/PHP/RENT%20A%20BIKE/rent-a-velo/public/` (adapter le port de MAMP).

## Inscription et modification des vélos

- « Créer un compte » ouvre l'inscription publique : prénom, nom, email, mot de passe et confirmation. Le compte créé a toujours le rôle `citizen`.
- Les champs de mot de passe (connexion, inscription et création de compte employé) proposent une icône œil pour afficher le mot de passe et un œil barré pour le masquer lorsque JavaScript est activé. Le mot de passe reste masqué par défaut ; le bouton ne soumet pas le formulaire et conserve un libellé accessible « Afficher / Masquer le mot de passe ».
- Les usagers inscrits se connectent via « Connexion » et restent sur le catalogue public, sans accès au tableau de bord. Leurs coordonnées sont préremplies et leurs demandes de réservation sont liées à leur compte.
- « Mes réservations » affiche uniquement les demandes du compte connecté, de la plus récente à la plus ancienne : numéro, vélo, date de demande, période et statut (en attente, acceptée ou refusée). « Actualiser le suivi » recharge les décisions enregistrées par les employés. Un lien vers ce suivi est aussi proposé après la réservation.
- Depuis « Vélos » dans le tableau de bord, un administrateur utilise « Modifier » pour changer l'immatriculation, l'état en service / hors service et la photo. Sans nouvelle photo, l'ancienne est conservée.
- Les employés gardent la possibilité d'ajouter des vélos et de changer leur disponibilité, mais pas de modifier leur fiche.
- L'inscription et l'enregistrement des fiches vélo sont protégés par un jeton de formulaire. Les photos doivent être de véritables images JPG, PNG ou WEBP ; le dossier `public/img/velos/` doit être accessible en écriture.
- Aucun changement de structure de base n'est nécessaire : ne pas réimporter le script SQL sur une base existante (il supprime la base).

## Tests

Après `composer dump-autoload`, lancer `php tests/features.php` depuis la racine (sur Windows : `php tests\features.php`). Les tests utilisent SQLite en mémoire et ne modifient pas la base MAMP. Ils couvrent l'inscription, la connexion des usagers et employés, les restrictions du tableau de bord, les modifications de vélos et l'association des réservations au compte connecté. L'extension `pdo_sqlite` doit être activée.

## Comptes de test

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | admin@velo.fr | Admin123! |
| Employé | employe@velo.fr | Employe123! |

## Structure

- `public/index.php` : routeur (`?url=…` + `match`), `session_start()`.
- `src/Controllers/` : `BikeController`, `ReservationController`, `UserController`, `ErreurController`.
- `src/Models/` : entités (`User`, `Bike`, `Reservation`), Repository (requêtes PDO préparées), `Database` (Singleton) + `config.php`.
- `src/views/` : `layout.php` + une vue par page. `admin/partials/reservation-lignes.php` sert aussi à la recherche AJAX.
- `src/Helper/` : `hydrate()`, `render()` / `renderPartiel()`, contrôle d'accès (`exigerConnexion()`, `exigerAdmin()`).
- `public/js/recherche.js` : recherche dynamique avec `fetch()`.

## Base de données : corrections du script fourni

1. `users.login` : `INT` → `VARCHAR(100)`, l'identifiant est un email.
2. Ajout de `users.role` (`citizen`, `employee`, `admin`) : un usager peut réserver sans compte (mot de passe `NULL`) ou s'inscrire pour se connecter. Le tableau de bord reste réservé aux employés et administrateurs.
3. `reservations` : la clé primaire `(user_id, bike_id)` empêchait de réserver deux fois le même vélo. `res_num` devient la clé primaire auto-incrémentée (à partir de 1001) : c'est l'identifiant unique donné à l'usager.
4. `ON DELETE CASCADE` sur les clés étrangères : supprimer un compte supprime ses réservations.

## Règles métier

- Un vélo est réservable s'il est en service et si aucune réservation en attente ou acceptée ne chevauche les dates choisies.
- Les employés valident / refusent les demandes, enregistrent des vélos et créent des comptes employés.
- Seul l'administrateur supprime une réservation ou un compte, et crée d'autres administrateurs.
- Seul l'administrateur modifie la fiche complète d'un vélo.
- Mots de passe hachés avec `password_hash()`, vérifiés avec `password_verify()`.
