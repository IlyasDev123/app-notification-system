<?php

namespace App\Contracts;

interface EncryptionServiceInterface
{
    /**
     * Encrypt an array payload (e.g., API keys, secrets).
     */
    public function encryptArray(array $payload): string;

    /**
     * Decrypt an encrypted string payload back into an array.
     */
    public function decryptArray(string $encryptedPayload): array;
}
