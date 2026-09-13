<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class NotificationLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'channel',
        'provider',
        'recipient',
        'status',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
    ];
}
