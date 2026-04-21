<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAiSetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'provider',
        'dify_dataset_id',
        'dify_dataset_name',
        'dify_app_api_key',
        'dify_app_name',
        'dify_app_mode',
        'is_active',
        'dataset_bound',
        'dataset_bound_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'dataset_bound' => 'boolean',
        'dataset_bound_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
