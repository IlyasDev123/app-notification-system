<?php

namespace App\Services\Security;

use App\Contracts\EncryptionServiceInterface;
use Illuminate\Contracts\Encryption\Encrypter;

class LaravelEncryptionService implements EncryptionServiceInterface
{
    public function __construct(
        private readonly Encrypter $encrypter
    ) {}

    public function encryptArray(array $payload): string
    {
        $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);
        return $this->encrypter->encrypt($jsonPayload);
    }

    public function decryptArray(string $encryptedPayload): array
    {
        $decryptedJson = $this->encrypter->decrypt($encryptedPayload);
        return json_decode($decryptedJson, true, 512, JSON_THROW_ON_ERROR);
    }
}