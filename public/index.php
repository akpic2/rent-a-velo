<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\BikeController;
use App\Controllers\ErreurController;
use App\Controllers\ReservationController;
use App\Controllers\UserController;

// La session garde l'utilisateur connecté et les messages flash d'une page à l'autre
session_start();

// Ce fichier est le routeur : il lit ?url=... et appelle la bonne méthode du bon contrôleur
$url = $_GET['url'] ?? 'velos';
$id = (int) ($_GET['id'] ?? 0);

$bikeController = new BikeController();
$reservationController = new ReservationController();
$userController = new UserController();
$erreurController = new ErreurController();

match ($url) {
    // Interface publique
    'velos' => $bikeController->index(),
    'reserver' => $reservationController->create($id),
    'reservation-store' => $reservationController->store(),
    'confirmation' => $reservationController->confirmation($id),
    'mes-reservations' => $reservationController->myReservations(),

    // Inscription et connexion
    'inscription' => $userController->register(),
    'inscription-store' => $userController->storeRegistration(),
    'connexion' => $userController->login(),
    'connexion-verif' => $userController->checkLogin(),
    'deconnexion' => $userController->logout(),

    // Tableau de bord : réservations
    'admin' => $reservationController->adminIndex(),
    'admin-recherche' => $reservationController->search((string) ($_GET['q'] ?? '')),
    'reservation-valider' => $reservationController->accept(),
    'reservation-refuser' => $reservationController->reject(),
    'reservation-supprimer' => $reservationController->delete(),

    // Tableau de bord : vélos
    'admin-velos' => $bikeController->adminIndex(),
    'velo-create' => $bikeController->create(),
    'velo-store' => $bikeController->store(),
    'velo-edit' => $bikeController->edit($id),
    'velo-update' => $bikeController->update(),
    'velo-dispo' => $bikeController->toggleAvailability(),

    // Tableau de bord : comptes
    'admin-comptes' => $userController->index(),
    'compte-store' => $userController->store(),
    'compte-supprimer' => $userController->delete(),

    default => $erreurController->pageErreur(),
};
