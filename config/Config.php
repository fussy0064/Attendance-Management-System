<?php
/**
 * Config
 * Central configuration for DB connection and encryption key.
 * IMPORTANT: In production, load these from environment variables
 * (e.g. getenv()) instead of hardcoding, and never commit real secrets.
 */
class Config
{
    // Database
    const DB_HOST = 'localhost';
    const DB_NAME = 'attendance_system';
    const DB_USER = 'root';
    const DB_PASS = '';
    const DB_CHARSET = 'utf8mb4';

    // Encryption
    // 32+ character random secret used to derive the AES-256 key.
    // Change this before deploying, and keep it out of version control
    // in a real deployment (use an environment variable).
    const ENCRYPTION_KEY = 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET_KEY_2026';
}
