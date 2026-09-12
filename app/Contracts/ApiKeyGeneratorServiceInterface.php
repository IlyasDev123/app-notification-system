<?php

namespace App\Contracts;

interface ApiKeyGeneratorServiceInterface
{
    public function generate(string $prefix = 'tn_live_'): array;
}
