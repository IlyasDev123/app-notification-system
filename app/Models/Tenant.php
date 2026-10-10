<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TenantApiKey;
use App\Models\TenantProvider;

class Tenant extends Model
{
   use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function apiKeys(): HasMany
    {
        return $this->hasMany(TenantApiKey::class);
    }

    public function providers(): HasMany
    {
        return $this->hasMany(TenantProvider::class);
    }
}
