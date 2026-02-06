<?php

declare(strict_types=1);

class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, password, created_at FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, password, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function emailTakenByAnotherUser(string $email, int $currentUserId): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
        $stmt->execute([
            'email' => $email,
            'id' => $currentUserId,
        ]);

        return (bool) $stmt->fetch();
    }

    public function create(string $name, string $email, string $hashedPassword): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, :created_at)'
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateProfile(int $id, string $name, string $email): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'id' => $id,
        ]);
    }

    public function updatePassword(int $id, string $hashedPassword): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute([
            'password' => $hashedPassword,
            'id' => $id,
        ]);
    }
}
