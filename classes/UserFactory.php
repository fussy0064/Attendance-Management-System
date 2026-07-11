<?php
require_once __DIR__ . '/Admin.php';
require_once __DIR__ . '/Teacher.php';

/**
 * UserFactory
 * Creates the correct User subclass from a raw DB row, based on the
 * stored role. Callers work only with the abstract User type after
 * this point -> polymorphism at the point of use.
 */
class UserFactory
{
    public static function create(array $row): User
    {
        return match ($row['role']) {
            'admin'   => new Admin($row['username'], $row['password_hash'], $row['email'], (int) $row['id']),
            'teacher' => new Teacher($row['username'], $row['password_hash'], $row['email'], (int) $row['id']),
            default   => throw new InvalidArgumentException('Unknown role: ' . $row['role']),
        };
    }
}
