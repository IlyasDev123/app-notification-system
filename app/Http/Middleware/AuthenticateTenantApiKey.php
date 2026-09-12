<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\TenantApiKey;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateTenantApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (!$bearerToken || !str_contains($bearerToken, '.')) {
            return response()->json(['error' => 'Unauthorized. Missing or invalid API Key structure.'], 401);
        }

        [$identifier] = explode('.', $bearerToken, 2);
        $keyHash = hash('sha256', $bearerToken);

        // Fast-lookup by identifier, then verify key hash
        $apiKey = TenantApiKey::where('key_identifier', $identifier)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$apiKey || !hash_equals($apiKey->key_hash, $keyHash)) {
            return response()->json(['error' => 'Unauthorized. Invalid API key.'], 401);
        }

        // Update key activity timestamp
        $apiKey->updateQuietly(['last_used_at' => now()]);

        // Inject active tenant model context into request thread
        $request->setUserResolver(fn () => $apiKey->tenant);

        return $next($request);
    }
}
