<?php

/**
 * User (abstract)
 * Base class for anyone who can log in to the system: Admin or Teacher.
 * getAllowedActions() is overridden per role -> polymorphism, so the
 * dashboard can render different menus/actions for the same call site:
 *      foreach ($user->getAllowedActions() as $action) { ... }
 */
abstract class User
{
    private ?int $id;
    private string $username;
    private string $passwordHash;
    private string $email;

    public function __construct(string $username, string $passwordHash, string $email, ?int $id = null)
    {
        $this->id           = $id;
        $this->username     = $username;
        $this->passwordHash = $passwordHash;
        $this->email        = $email;
    }

    abstract public function getRole(): string;
    abstract public function getAllowedActions(): array;

    public function getId(): ?int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->passwordHash);
    }
}
