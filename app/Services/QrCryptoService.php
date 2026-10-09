<?php

namespace App\Services;

class QrCryptoService
{
    private const CIPHER = 'AES-256-CBC';
    private const PREFIX = 'PUPSJ-QR:';

    /**
     * Get or derive the AES encryption key (32 bytes).
     */
    private static function getKey(): string
    {
        $secret = config('app.qr_secret') ?: config('app.key') ?: 'pupsj-libris-library-aes-key-2026';
        // Ensure 32-byte key for AES-256
        return hash('sha256', $secret, true);
    }

    /**
     * Encrypt a string payload (e.g. "BOOK:15", "STUDENT:3") using AES-256-CBC.
     */
    public static function encrypt(string $payload): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER));
        $encrypted = openssl_encrypt($payload, self::CIPHER, self::getKey(), OPENSSL_RAW_DATA, $iv);
        
        return self::PREFIX . base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt an encrypted QR code string.
     * If the payload is not encrypted (e.g. raw barcode or plain identifier), returns it as-is.
     */
    public static function decrypt(string $input): string
    {
        $trimmed = trim($input);

        if (!str_starts_with($trimmed, self::PREFIX)) {
            // Already plain text / barcode / unencrypted
            return $trimmed;
        }

        $rawBase64 = substr($trimmed, strlen(self::PREFIX));
        $decoded = base64_decode($rawBase64, true);

        if ($decoded === false) {
            return $trimmed;
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        if (strlen($decoded) <= $ivLength) {
            return $trimmed;
        }

        $iv = substr($decoded, 0, $ivLength);
        $ciphertext = substr($decoded, $ivLength);

        $decrypted = openssl_decrypt($ciphertext, self::CIPHER, self::getKey(), OPENSSL_RAW_DATA, $iv);

        return $decrypted !== false ? trim($decrypted) : $trimmed;
    }

    /**
     * Check if a string is in the encrypted QR format.
     */
    public static function isEncrypted(string $input): bool
    {
        return str_starts_with(trim($input), self::PREFIX);
    }
}
