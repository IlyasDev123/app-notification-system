<?php

use App\Services\Security\ApiKeyGeneratorService;

it('generates a valid API key payload structure', function () {
    $result = (new ApiKeyGeneratorService())->generate('tn_live_');

    expect($result)
        ->toHaveKeys(['identifier', 'full_key', 'hash'])
        ->and($result['identifier'])->toBeString()->toStartWith('tn_live_')
        ->and($result['full_key'])->toBeString()->toStartWith($result['identifier'] . '.')
        ->and($result['hash'])->toBeString()->toHaveLength(64);
});
