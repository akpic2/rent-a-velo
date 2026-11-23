<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class BikeRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnexion();
    }

    public function findAll(): array
    {
        $query = $this->pdo->prepare("SELECT * FROM bikes ORDER BY registration_number");
        $query->execute();

        $bikes = [];
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
            $bikes[] = hydrate(Bike::class, $ligne);
        }

        return $bikes;
    }

    public function findById(int $bikeId): ?Bike
    {
        $query = $this->pdo->prepare("SELECT * FROM bikes WHERE bike_id = :bike_id");
        $query->bindValue(':bike_id', $bikeId, PDO::PARAM_INT);
        $query->execute();

        $ligne = $query->fetch(PDO::FETCH_ASSOC);

        if ($ligne === false) {
            return null;
        }

        return hydrate(Bike::class, $ligne);
    }

    public function insert(Bike $bike): int
    {
        $sql = "INSERT INTO bikes (registration_number, availability, photo) VALUES (:registration_number, :availability, :photo)";
        $query = $this->pdo->prepare($sql);
        $query->execute([
            'registration_number' => $bike->getRegistrationNumber(),
            'availability' => $bike->getAvailability(),
            'photo' => $bike->getPhoto(),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateAvailability(int $bikeId, int $availability): void
    {
        $query = $this->pdo->prepare("UPDATE bikes SET availability = :availability WHERE bike_id = :bike_id");
        $query->bindValue(':availability', $availability, PDO::PARAM_INT);
        $query->bindValue(':bike_id', $bikeId, PDO::PARAM_INT);
        $query->execute();
    }
}
