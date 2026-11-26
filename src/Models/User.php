<?php

declare(strict_types=1);

namespace App\Models;

class User
{
    private ?int $userId = null;
    private string $firstname = '';
    private string $lastname = '';
    private string $login = '';
    // null pour une réservation effectuée sans compte
    private ?string $password = null;
    private string $role = 'citizen';

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getRoleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Administrateur',
            'employee' => 'Employé',
            default => 'Usager',
        };
    }
}
