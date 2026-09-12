<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant;

class TenantApiKey extends Model
{
use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'name',
        'key_identifier',
        'key_hash',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'key_hash',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Generate a new API key pair (Identifier & Raw Secret)
     */
    public static function generateSecretKey(): array
    {
        $prefix = 'tn_live_';
        $identifier = $prefix . bin2hex(random_bytes(4)); // 16 char prefix lookup
        $secret = bin2hex(random_bytes(24));
        $fullKey = "{$identifier}.{$secret}";

        return [
            'identifier' => $identifier,
            'full_key'   => $fullKey,
            'hash'       => hash('sha256', $fullKey),
        ];
    }
}
