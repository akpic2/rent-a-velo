<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BikeRepository;
use App\Models\Reservation;
use App\Models\ReservationRepository;
use App\Models\User;
use App\Models\UserRepository;
use DateTime;

class ReservationController
{
    /* ---------- Interface publique ---------- */

    public function create(int $bikeId): void
    {
        $bikeRepository = new BikeRepository();
        $bike = $bikeRepository->findById($bikeId);

        if ($bike === null || $bike->getAvailability() === 0) {
            http_response_code(404);
            render('reservations/introuvable', 'Vélo introuvable');
            return;
        }

        render('reservations/form', 'Réserver le vélo ' . $bike->getRegistrationNumber(), $this->donneesFormulaire($bike->getBikeId()));
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        $bikeId = (int) ($_POST['bike_id'] ?? 0);
        $firstname = trim($_POST['firstname'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $login = trim($_POST['login'] ?? '');
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';

        $bikeRepository = new BikeRepository();
        $bike = $bikeRepository->findById($bikeId);

        if ($bike === null || $bike->getAvailability() === 0) {
            http_response_code(404);
            render('reservations/introuvable', 'Vélo introuvable');
            return;
        }

        $reservationRepository = new ReservationRepository();

        // Les dates au format AAAA-MM-JJ se comparent comme du texte : '2026-11-24' < '2026-11-30'
        $erreur = null;
        if ($firstname === '' || $lastname === '' || $login === '' || $startDate === '' || $endDate === '') {
            $erreur = 'Tous les champs sont obligatoires.';
        } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $erreur = 'L\'adresse email n\'est pas valide.';
        } elseif (!$this->dateValide($startDate) || !$this->dateValide($endDate)) {
            $erreur = 'Les dates ne sont pas valides.';
        } elseif ($startDate < date('Y-m-d')) {
            $erreur = 'La date de début ne peut pas être dans le passé.';
        } elseif ($endDate < $startDate) {
            $erreur = 'La date de fin doit être après la date de début.';
        } elseif (!$reservationRepository->isBikeFree($bikeId, $startDate, $endDate)) {
            $erreur = 'Ce vélo est déjà réservé sur ces dates. Choisissez une autre période.';
        }

        if ($erreur !== null) {
            render('reservations/form', 'Réserver le vélo ' . $bike->getRegistrationNumber(), $this->donneesFormulaire($bikeId, [
                'erreur' => $erreur,
                'saisie' => $_POST,
            ]));
            return;
        }

        // L'usager n'a pas de compte : on le retrouve par son email, ou on le crée sans mot de passe
        $userRepository = new UserRepository();
        $user = $userRepository->findByLogin($login);

        if ($user === null) {
            $user = new User();
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setLogin($login);
            $user->setRole('citizen');
            $userId = $userRepository->insert($user);
        } else {
            $userId = $user->getUserId();
        }

        $reservation = new Reservation();
        $reservation->setUserId($userId);
        $reservation->setBikeId($bikeId);
        $reservation->setStartDate($startDate);
        $reservation->setEndDate($endDate);

        $resNum = $reservationRepository->insert($reservation);

        header("Location: index.php?url=confirmation&id={$resNum}");
        exit;
    }

    // Confirmation avec l'identifiant unique (sert aussi à suivre sa demande)
    public function confirmation(int $resNum): void
    {
        $repository = new ReservationRepository();
        $reservation = $repository->findByNum($resNum);

        if ($reservation === null) {
            http_response_code(404);
            render('reservations/introuvable', 'Réservation introuvable');
            return;
        }

        render('reservations/confirmation', "Réservation n° {$resNum}", ['reservation' => $reservation]);
    }

    /* ---------- Tableau de bord employés ---------- */

    public function adminIndex(): void
    {
        exigerConnexion();

        $repository = new ReservationRepository();
        $reservations = $repository->findPending();

        render('admin/reservations', 'Réservations en attente', ['reservations' => $reservations]);
    }

    // Appelée en AJAX par public/js/recherche.js : renvoie seulement les lignes du tableau
    public function search(string $texte): void
    {
        exigerConnexion();

        $repository = new ReservationRepository();
        $texte = trim($texte);

        // Champ vidé : on revient à la liste des demandes en attente
        if ($texte === '') {
            $reservations = $repository->findPending();
        } else {
            $reservations = $repository->search($texte);
        }

        renderPartiel('admin/partials/reservation-lignes', ['reservations' => $reservations]);
    }

    public function accept(): void
    {
        $this->changerStatut('accepted', 'validée');
    }

    public function reject(): void
    {
        $this->changerStatut('rejected', 'refusée');
    }

    public function delete(): void
    {
        exigerAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        $resNum = (int) ($_POST['res_num'] ?? 0);

        $repository = new ReservationRepository();
        $repository->delete($resNum);

        $_SESSION['flash'] = "Réservation n° {$resNum} supprimée.";
        header('Location: index.php?url=admin');
        exit;
    }

    /* ---------- Méthodes internes ---------- */

    private function changerStatut(string $status, string $message): void
    {
        exigerConnexion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        $resNum = (int) ($_POST['res_num'] ?? 0);

        $repository = new ReservationRepository();
        $repository->updateStatus($resNum, $status);

        $_SESSION['flash'] = "Réservation n° {$resNum} {$message}.";
        header('Location: index.php?url=admin');
        exit;
    }

    /**
     * Données communes au formulaire : le vélo + ses périodes déjà prises.
     */
    private function donneesFormulaire(int $bikeId, array $autres = []): array
    {
        $bikeRepository = new BikeRepository();
        $reservationRepository = new ReservationRepository();

        return array_merge([
            'bike' => $bikeRepository->findById($bikeId),
            'periodesPrises' => $reservationRepository->findUpcomingByBike($bikeId),
            'saisie' => [],
        ], $autres);
    }

    /**
     * Vrai si la chaîne est une vraie date AAAA-MM-JJ (refuse « 2026-02-31 »).
     */
    private function dateValide(string $date): bool
    {
        $objet = DateTime::createFromFormat('Y-m-d', $date);

        return $objet !== false && $objet->format('Y-m-d') === $date;
    }
}
