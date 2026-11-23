<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class ReservationRepository
{
    private const SELECT_RESERVATIONS = "SELECT reservations.*, users.firstname, users.lastname, users.login, bikes.registration_number FROM reservations INNER JOIN users ON reservations.user_id = users.user_id INNER JOIN bikes ON reservations.bike_id = bikes.bike_id";

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnexion();
    }

    /**
     * Les demandes à traiter, la plus ancienne en premier.
     */
    public function findPending(): array
    {
        $query = $this->pdo->prepare(self::SELECT_RESERVATIONS . " WHERE reservations.status = 'pending' ORDER BY reservations.res_date, reservations.res_num");
        $query->execute();

        return $this->hydrateAll($query->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findByNum(int $resNum): ?Reservation
    {
        $query = $this->pdo->prepare(self::SELECT_RESERVATIONS . " WHERE reservations.res_num = :res_num");
        $query->bindValue(':res_num', $resNum, PDO::PARAM_INT);
        $query->execute();

        $ligne = $query->fetch(PDO::FETCH_ASSOC);

        if ($ligne === false) {
            return null;
        }

        return hydrate(Reservation::class, $ligne);
    }

    /**
     * Les périodes déjà prises pour un vélo (en attente ou acceptées), à partir d'aujourd'hui.
     */
    public function findUpcomingByBike(int $bikeId): array
    {
        $query = $this->pdo->prepare(self::SELECT_RESERVATIONS . " WHERE reservations.bike_id = :bike_id AND reservations.status <> 'rejected' AND reservations.end_date >= CURDATE() ORDER BY reservations.start_date");
        $query->bindValue(':bike_id', $bikeId, PDO::PARAM_INT);
        $query->execute();

        return $this->hydrateAll($query->fetchAll(PDO::FETCH_ASSOC));
    }

    public function search(string $texte): array
    {
        // CONCAT colle les colonnes en une seule chaîne : un seul LIKE suffit pour chercher partout
        $sql = self::SELECT_RESERVATIONS . " WHERE CONCAT(reservations.res_num, ' ', users.firstname, ' ', users.lastname, ' ', users.login, ' ', bikes.registration_number) LIKE :texte ORDER BY reservations.res_date DESC";
        $query = $this->pdo->prepare($sql);
        $query->bindValue(':texte', '%' . $texte . '%', PDO::PARAM_STR);
        $query->execute();

        return $this->hydrateAll($query->fetchAll(PDO::FETCH_ASSOC));
    }

    public function isBikeFree(int $bikeId, string $startDate, string $endDate): bool
    {
        // Deux périodes se chevauchent si l'une commence avant la fin de l'autre ET finit après son début
        $sql = "SELECT COUNT(*) FROM reservations WHERE bike_id = :bike_id AND status <> 'rejected' AND start_date <= :end_date AND end_date >= :start_date";
        $query = $this->pdo->prepare($sql);
        $query->execute([
            'bike_id' => $bikeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return (int) $query->fetchColumn() === 0;
    }

    public function insert(Reservation $reservation): int
    {
        $sql = "INSERT INTO reservations (user_id, bike_id, start_date, end_date) VALUES (:user_id, :bike_id, :start_date, :end_date)";
        $query = $this->pdo->prepare($sql);
        $query->execute([
            'user_id' => $reservation->getUserId(),
            'bike_id' => $reservation->getBikeId(),
            'start_date' => $reservation->getStartDate(),
            'end_date' => $reservation->getEndDate(),
        ]);

        // res_num auto-incrémenté : c'est l'identifiant unique donné à l'usager
        return (int) $this->pdo->lastInsertId();
    }

    public function updateStatus(int $resNum, string $status): void
    {
        $query = $this->pdo->prepare("UPDATE reservations SET status = :status WHERE res_num = :res_num");
        $query->bindValue(':status', $status, PDO::PARAM_STR);
        $query->bindValue(':res_num', $resNum, PDO::PARAM_INT);
        $query->execute();
    }

    public function delete(int $resNum): void
    {
        $query = $this->pdo->prepare("DELETE FROM reservations WHERE res_num = :res_num");
        $query->bindValue(':res_num', $resNum, PDO::PARAM_INT);
        $query->execute();
    }

    private function hydrateAll(array $lignes): array
    {
        $reservations = [];
        foreach ($lignes as $ligne) {
            $reservations[] = hydrate(Reservation::class, $ligne);
        }

        return $reservations;
    }
}
