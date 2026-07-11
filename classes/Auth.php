<?php
require_once __DIR__ . '/../repositories/UserRepository.php';

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function attemptLogin(string $username, string $password): bool
    {
        $repo = new UserRepository();
        $user = $repo->findByUsername($username);

        if ($user === null || !$user->verifyPassword($password)) {
            return false;
        }

        self::start();
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['role']     = $user->getRole();

        return true;
    }

    public static function check(): bool
    {
        self::start();
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin(): void
    {
        self::start();
        if (!self::check()) {
            header('Location: index.php');
            exit;
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();
        if ($_SESSION['role'] !== $role) {
            header('Location: dashboard.php');
            exit;
        }
    }

    public static function role(): ?string
    {
        self::start();
        return $_SESSION['role'] ?? null;
    }

    public static function username(): ?string
    {
        self::start();
        return $_SESSION['username'] ?? null;
    }

    public static function userId(): ?int
    {
        self::start();
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }
}
