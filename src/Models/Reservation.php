<?php

declare(strict_types=1);

namespace App\Models;

class Reservation
{
    private ?int $resNum = null;
    private int $userId = 0;
    private int $bikeId = 0;
    private string $resDate = '';
    private string $startDate = '';
    private string $endDate = '';
    private string $status = 'pending';

    // Données jointes (users et bikes), remplies par les requêtes avec INNER JOIN
    private ?string $firstname = null;
    private ?string $lastname = null;
    private ?string $login = null;
    private ?string $registrationNumber = null;

    public function getResNum(): ?int
    {
        return $this->resNum;
    }

    public function setResNum(?int $resNum): void
    {
        $this->resNum = $resNum;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getBikeId(): int
    {
        return $this->bikeId;
    }

    public function setBikeId(int $bikeId): void
    {
        $this->bikeId = $bikeId;
    }

    public function getResDate(): string
    {
        return $this->resDate;
    }

    public function setResDate(string $resDate): void
    {
        $this->resDate = $resDate;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function setStartDate(string $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }

    public function setEndDate(string $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(?string $login): void
    {
        $this->login = $login;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): void
    {
        $this->registrationNumber = $registrationNumber;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'accepted' => 'Acceptée',
            'rejected' => 'Refusée',
            default => 'En attente',
        };
    }
}
