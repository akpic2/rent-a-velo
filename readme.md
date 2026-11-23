# Rent a Vélo — TP location de vélos (POO, MVC, Singleton, AJAX)

## Installation (MAMP)

1. Importer `sql/rent_a_velo.sql` dans phpMyAdmin (crée la base `rent_a_velo` + des données de test).
2. Vérifier les identifiants dans `src/Models/config.php`.
3. À la racine du projet : `composer dump-autoload`.
4. Ouvrir `http://localhost:81/B2/MVC/tp%20rent%20a%20velo/public/` (adapter le port).

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
2. Ajout de `users.role` (`citizen`, `employee`, `admin`) : un usager réserve sans compte (mot de passe `NULL`), seuls employés et admin se connectent.
3. `reservations` : la clé primaire `(user_id, bike_id)` empêchait de réserver deux fois le même vélo. `res_num` devient la clé primaire auto-incrémentée (à partir de 1001) : c'est l'identifiant unique donné à l'usager.
4. `ON DELETE CASCADE` sur les clés étrangères : supprimer un compte supprime ses réservations.

## Règles métier

- Un vélo est réservable s'il est en service et si aucune réservation en attente ou acceptée ne chevauche les dates choisies.
- Les employés valident / refusent les demandes, enregistrent des vélos et créent des comptes employés.
- Seul l'administrateur supprime une réservation ou un compte, et crée d'autres administrateurs.
- Mots de passe hachés avec `password_hash()`, vérifiés avec `password_verify()`.
