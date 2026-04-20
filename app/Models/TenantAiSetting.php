<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAiSetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'provider',
        'dify_base_url',
        'dify_app_api_key',
        'dify_dataset_api_key',
        'dify_dataset_id',
        'dify_app_mode',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
