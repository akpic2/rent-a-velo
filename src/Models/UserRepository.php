<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnexion();
    }

    public function findAll(): array
    {
        $query = $this->pdo->prepare("SELECT * FROM users ORDER BY role, lastname, firstname");
        $query->execute();

        $users = [];
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
            $users[] = hydrate(User::class, $ligne);
        }

        return $users;
    }

    public function findByLogin(string $login): ?User
    {
        $query = $this->pdo->prepare("SELECT * FROM users WHERE login = :login");
        $query->bindValue(':login', $login, PDO::PARAM_STR);
        $query->execute();

        $ligne = $query->fetch(PDO::FETCH_ASSOC);

        if ($ligne === false) {
            return null;
        }

        return hydrate(User::class, $ligne);
    }

    public function insert(User $user): int
    {
        $sql = "INSERT INTO users (firstname, lastname, login, password, role) VALUES (:firstname, :lastname, :login, :password, :role)";
        $query = $this->pdo->prepare($sql);
        $query->execute([
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'login' => $user->getLogin(),
            'password' => $user->getPassword(),
            'role' => $user->getRole(),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $userId): void
    {
        // Ses réservations partent avec lui (ON DELETE CASCADE dans le SQL)
        $query = $this->pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();
    }
}
