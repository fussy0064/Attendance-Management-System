<?php
require_once __DIR__ . '/Config.php';

/**
 * Database
 * Singleton wrapper around a PDO connection.
 * Demonstrates encapsulation: the PDO instance is private and only
 * reachable through getConnection().
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . Config::DB_HOST .
                   ";dbname=" . Config::DB_NAME .
                   ";charset=" . Config::DB_CHARSET;

            $this->connection = new PDO($dsn, Config::DB_USER, Config::DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    // Prevent cloning of the singleton
    private function __clone() {}
}
