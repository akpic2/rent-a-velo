<?php

declare(strict_types=1);

namespace App\Models;

class Bike
{
    private ?int $bikeId = null;
    private string $registrationNumber = '';
    // BOOLEAN en MySQL = TINYINT : la base renvoie 1 (en service) ou 0 (hors service)
    private int $availability = 1;
    private ?string $photo = null;

    public function getBikeId(): ?int
    {
        return $this->bikeId;
    }

    public function setBikeId(?int $bikeId): void
    {
        $this->bikeId = $bikeId;
    }

    public function getRegistrationNumber(): string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(string $registrationNumber): void
    {
        $this->registrationNumber = $registrationNumber;
    }

    public function getAvailability(): int
    {
        return $this->availability;
    }

    public function setAvailability(int $availability): void
    {
        $this->availability = $availability;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): void
    {
        $this->photo = $photo;
    }

    /**
     * Chemin de l'image à afficher (image par défaut si le vélo n'a pas de photo).
     */
    public function getPhotoUrl(): string
    {
        if ($this->photo === null) {
            return 'img/velo-defaut.svg';
        }

        return 'img/velos/' . $this->photo;
    }
}
