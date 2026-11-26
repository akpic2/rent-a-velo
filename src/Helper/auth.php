<?php

declare(strict_types=1);

// Petites fonctions de contrôle d'accès, appelées en tête des actions du tableau de bord

function estConnecte(): bool
{
    return isset($_SESSION['user_id']);
}

function estAdmin(): bool
{
    return estConnecte() && $_SESSION['role'] === 'admin';
}

function estEmploye(): bool
{
    return estConnecte() && in_array($_SESSION['role'] ?? '', ['employee', 'admin'], true);
}

function exigerConnexion(): void
{
    if (!estConnecte()) {
        $_SESSION['flash'] = 'Connectez-vous pour accéder au tableau de bord.';
        header('Location: index.php?url=connexion');
        exit;
    }
}

function exigerAdmin(): void
{
    exigerEmploye();

    if (!estAdmin()) {
        $_SESSION['flash'] = 'Action réservée à l\'administrateur.';
        header('Location: index.php?url=admin');
        exit;
    }
}

function exigerEmploye(): void
{
    exigerConnexion();

    if (!estEmploye()) {
        $_SESSION['flash'] = 'Le tableau de bord est réservé aux employés et administrateurs.';
        header('Location: index.php?url=velos');
        exit;
    }
}

function jetonCsrf(): string
{
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function verifierCsrf(): bool
{
    $jeton = $_POST['csrf'] ?? null;
    if (!is_string($jeton) || !isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $jeton)) {
        http_response_code(403);
        render('errors/requete', 'Requête refusée', [
            'erreur' => 'Le formulaire a expiré. Revenez à la page précédente et rechargez-la.',
        ]);
        return false;
    }

    return true;
}
