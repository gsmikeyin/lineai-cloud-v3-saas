<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantLineChannel extends Model
{
    protected $fillable = [
        'tenant_id',
        'provider',
        'channel_name',
        'channel_id',
        'channel_secret',
        'channel_access_token',
        'basic_id',
        'bot_user_id',
        'webhook_url',
        'is_active',
        'is_verified',
        'last_webhook_at',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'last_webhook_at' => 'datetime',
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}