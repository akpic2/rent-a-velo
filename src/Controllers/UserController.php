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
            header('Location: index.php?url=' . (estEmploye() ? 'admin' : 'velos'));
            exit;
        }

        render('users/connexion', 'Connexion');
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

        // Une réservation sans compte ne donne pas de mot de passe.
        // Même message dans tous les cas, pour ne pas dire si l'email existe.
        if ($user === null || $user->getPassword() === null || !password_verify($password, $user->getPassword())) {
            render('users/connexion', 'Connexion', [
                'erreur' => 'Identifiant ou mot de passe incorrect.',
                'login' => $login,
            ]);
            return;
        }

        // Nouvel identifiant de session après connexion (contre le vol de session)
        session_regenerate_id(true);

        // Identité et rôle uniquement : jamais le mot de passe en session
        $_SESSION['user_id'] = $user->getUserId();
        $_SESSION['firstname'] = $user->getFirstname();
        $_SESSION['lastname'] = $user->getLastname();
        $_SESSION['login'] = $user->getLogin();
        $_SESSION['role'] = $user->getRole();

        $_SESSION['flash'] = "Bonjour {$user->getFirstname()} !";
        header('Location: index.php?url=' . (estEmploye() ? 'admin' : 'velos'));
        exit;
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();

        header('Location: index.php');
        exit;
    }

    public function register(): void
    {
        if (estConnecte()) {
            header('Location: index.php?url=velos');
            exit;
        }

        render('users/inscription', 'Créer un compte', ['saisie' => []]);
    }

    public function storeRegistration(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        if (estConnecte()) {
            header('Location: index.php?url=velos');
            exit;
        }

        if (!verifierCsrf()) {
            return;
        }

        $firstname = trim(is_string($_POST['firstname'] ?? null) ? $_POST['firstname'] : '');
        $lastname = trim(is_string($_POST['lastname'] ?? null) ? $_POST['lastname'] : '');
        $login = trim(is_string($_POST['login'] ?? null) ? $_POST['login'] : '');
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $confirmation = is_string($_POST['password_confirmation'] ?? null) ? $_POST['password_confirmation'] : '';
        $saisie = compact('firstname', 'lastname', 'login');
        $repository = new UserRepository();

        $erreur = null;
        if ($firstname === '' || $lastname === '' || $login === '' || $password === '') {
            $erreur = 'Tous les champs sont obligatoires.';
        } elseif (strlen($firstname) > 255 || strlen($lastname) > 255 || strlen($login) > 100) {
            $erreur = 'Le nom et le prénom sont limités à 255 octets, et l\'email à 100 octets.';
        } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $erreur = 'L\'identifiant doit être une adresse email.';
        } elseif (strlen($password) < 8 || strlen($password) > 72 || str_contains($password, "\0")) {
            $erreur = 'Le mot de passe doit faire entre 8 et 72 octets, sans caractère nul.';
        } elseif ($password !== $confirmation) {
            $erreur = 'Les mots de passe ne correspondent pas.';
        } elseif ($repository->findByLogin($login) !== null) {
            $erreur = 'Cette adresse email est déjà utilisée.';
        }

        if ($erreur !== null) {
            render('users/inscription', 'Créer un compte', compact('erreur', 'saisie'));
            return;
        }

        $user = new User();
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setLogin($login);
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
        $user->setRole('citizen');

        try {
            $repository->insert($user);
        } catch (\PDOException $exception) {
            if (($exception->errorInfo[1] ?? null) !== 1062) {
                throw $exception;
            }
            render('users/inscription', 'Créer un compte', [
                'erreur' => 'Cette adresse email est déjà utilisée.',
                'saisie' => $saisie,
            ]);
            return;
        }

        $_SESSION['flash'] = 'Votre compte a été créé. Vous pouvez maintenant vous connecter.';
        header('Location: index.php?url=connexion');
        exit;
    }

    /* ---------- Gestion des comptes ---------- */

    public function index(): void
    {
        exigerEmploye();

        render('admin/users', 'Comptes utilisateurs', $this->donneesPage());
    }

    public function store(): void
    {
        exigerEmploye();

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
