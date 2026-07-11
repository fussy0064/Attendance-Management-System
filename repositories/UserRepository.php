<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Encryption.php';
require_once __DIR__ . '/../classes/UserFactory.php';

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $row['email'] = Encryption::decrypt($row['email_enc']);
        return UserFactory::create($row);
    }

    public function create(string $username, string $plainPassword, string $email, string $role): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, password_hash, email_enc, role)
             VALUES (:username, :password_hash, :email_enc, :role)'
        );
        $stmt->execute([
            'username'      => $username,
            'password_hash' => password_hash($plainPassword, PASSWORD_DEFAULT),
            'email_enc'     => Encryption::encrypt($email),
            'role'          => $role,
        ]);
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM users ORDER BY id ASC');
        $users = [];
        foreach ($stmt->fetchAll() as $row) {
            $row['email'] = Encryption::decrypt($row['email_enc']);
            $users[] = UserFactory::create($row);
        }
        return $users;
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
