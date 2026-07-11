<?php
require_once __DIR__ . '/../config/Config.php';

/**
 * Encryption
 * Provides AES-256-CBC encrypt/decrypt for row data before it is
 * persisted to, or after it is retrieved from, the database.
 *
 * Key management strategy:
 *  - A single application-wide secret (Config::ENCRYPTION_KEY) is
 *    stretched into a 256-bit key using SHA-256.
 *  - A random IV (initialization vector) is generated for every single
 *    value that is encrypted, so identical plaintexts never produce
 *    identical ciphertexts.
 *  - The IV is stored alongside the ciphertext (prepended, then
 *    base64-encoded as one string) so it is available again at
 *    decryption time. The IV is not secret; only the key is.
 */
class Encryption
{
    private static string $method = 'AES-256-CBC';

    private static function getKey(): string
    {
        // Derive a fixed-length 256-bit binary key from the configured secret.
        return hash('sha256', Config::ENCRYPTION_KEY, true);
    }

    public static function encrypt(?string $plainText): string
    {
        if ($plainText === null || $plainText === '') {
            return '';
        }

        $ivLength  = openssl_cipher_iv_length(self::$method);
        $iv        = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($plainText, self::$method, self::getKey(), 0, $iv);

        // Store IV + ciphertext together, base64-encoded for safe DB storage.
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt(?string $cipherText): string
    {
        if ($cipherText === null || $cipherText === '') {
            return '';
        }

        $raw      = base64_decode($cipherText);
        $ivLength = openssl_cipher_iv_length(self::$method);
        $iv       = substr($raw, 0, $ivLength);
        $encrypted = substr($raw, $ivLength);

        $decrypted = openssl_decrypt($encrypted, self::$method, self::getKey(), 0, $iv);

        return $decrypted === false ? '' : $decrypted;
    }
}
