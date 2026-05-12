<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageView extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'route_name',
        'page_title',
        'path',
        'view_date',
        'viewed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'view_date' => 'date',
        'viewed_at' => 'datetime',
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
