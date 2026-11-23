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
    exigerConnexion();

    if (!estAdmin()) {
        $_SESSION['flash'] = 'Action réservée à l\'administrateur.';
        header('Location: index.php?url=admin');
        exit;
    }
}
