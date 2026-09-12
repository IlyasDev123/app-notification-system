<?php

namespace App\Services\Security;

use App\Contracts\ApiKeyGeneratorServiceInterface;

class ApiKeyGeneratorService implements ApiKeyGeneratorServiceInterface
{
    public function generate(string $prefix = 'tn_live_'): array
    {
        $identifier = $prefix . bin2hex(random_bytes(4)); // 16-char identifier
        $secret = bin2hex(random_bytes(24));             // Secret portion
        $fullKey = "{$identifier}.{$secret}";

        return [
            'identifier' => $identifier,
            'full_key'   => $fullKey,
            'hash'       => hash('sha256', $fullKey),
        ];
    }
}