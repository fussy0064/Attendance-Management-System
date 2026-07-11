<?php
/**
 * seed.php
 * Run this ONCE from the command line (php seed.php) after creating the
 * database with sql/schema.sql, to create a default admin account.
 * Delete this file or restrict access to it after use in production.
 */
require_once __DIR__ . '/repositories/UserRepository.php';

$repo = new UserRepository();

$username = 'admin';
$password = 'Admin@123';
$email    = 'admin@example.com';

if ($repo->usernameExists($username)) {
    echo "Admin account '$username' already exists. Nothing to do.\n";
    exit;
}

$repo->create($username, $password, $email, 'admin');

echo "Default admin account created.\n";
echo "Username: $username\n";
echo "Password: $password\n";
echo "Please log in and consider creating your own admin account, then remove this default one.\n";
