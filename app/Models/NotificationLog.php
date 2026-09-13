<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

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
