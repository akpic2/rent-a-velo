<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\UserRepository;

class UserController
{
    /* ---------- Connexion / déconnexion ---------- */

    public function login(): void
    {
        if (estConnecte()) {
            header('Location: index.php?url=admin');
            exit;
        }

        render('users/connexion', 'Connexion employés');
    }

    public function checkLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        $repository = new UserRepository();
        $user = $repository->findByLogin($login);

        // Un usager n'a pas de mot de passe : il ne peut pas se connecter.
        // Même message dans tous les cas, pour ne pas dire si l'email existe.
        if ($user === null || $user->getPassword() === null || !password_verify($password, $user->getPassword())) {
            render('users/connexion', 'Connexion employés', [
                'erreur' => 'Identifiant ou mot de passe incorrect.',
                'login' => $login,
            ]);
            return;
        }

        // Nouvel identifiant de session après connexion (contre le vol de session)
        session_regenerate_id(true);

        // On garde seulement l'id, le prénom et le rôle : jamais le mot de passe en session
        $_SESSION['user_id'] = $user->getUserId();
        $_SESSION['firstname'] = $user->getFirstname();
        $_SESSION['role'] = $user->getRole();

        $_SESSION['flash'] = "Bonjour {$user->getFirstname()} !";
        header('Location: index.php?url=admin');
        exit;
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();

        header('Location: index.php');
        exit;
    }

    /* ---------- Gestion des comptes ---------- */

    public function index(): void
    {
        exigerConnexion();

        render('admin/users', 'Comptes utilisateurs', $this->donneesPage());
    }

    public function store(): void
    {
        exigerConnexion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        $firstname = trim($_POST['firstname'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        // Seul l'administrateur peut créer un autre administrateur
        $role = estAdmin() && ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'employee';

        $repository = new UserRepository();

        $erreur = null;
        if ($firstname === '' || $lastname === '' || $login === '' || $password === '') {
            $erreur = 'Tous les champs sont obligatoires.';
        } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $erreur = 'L\'identifiant doit être une adresse email.';
        } elseif (strlen($password) < 8) {
            $erreur = 'Le mot de passe doit faire au moins 8 caractères.';
        } elseif ($repository->findByLogin($login) !== null) {
            $erreur = 'Cette adresse email est déjà utilisée.';
        }

        if ($erreur !== null) {
            render('admin/users', 'Comptes utilisateurs', $this->donneesPage([
                'erreur' => $erreur,
                'saisie' => $_POST,
            ]));
            return;
        }

        $user = new User();
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setLogin($login);
        // Chiffrement du mot de passe : la base ne stocke que le hash
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
        $user->setRole($role);

        $repository->insert($user);

        $_SESSION['flash'] = "Le compte de {$firstname} {$lastname} a été créé.";
        header('Location: index.php?url=admin-comptes');
        exit;
    }

    public function delete(): void
    {
        exigerAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        $userId = (int) ($_POST['user_id'] ?? 0);

        // L'admin ne peut pas supprimer son propre compte (il se retrouverait bloqué dehors)
        if ($userId === $_SESSION['user_id']) {
            $_SESSION['flash'] = 'Vous ne pouvez pas supprimer votre propre compte.';
            header('Location: index.php?url=admin-comptes');
            exit;
        }

        $repository = new UserRepository();
        $repository->delete($userId);

        $_SESSION['flash'] = 'Compte supprimé.';
        header('Location: index.php?url=admin-comptes');
        exit;
    }

    private function donneesPage(array $autres = []): array
    {
        $repository = new UserRepository();

        return array_merge([
            'users' => $repository->findAll(),
            'saisie' => [],
        ], $autres);
    }
}
