<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthEvent extends Model
{
    public const TYPE_LOGIN = 'login';
    public const TYPE_REGISTER = 'register';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'event_type',
        'provider',
        'event_date',
        'occurred_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'event_date' => 'date',
        'occurred_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
