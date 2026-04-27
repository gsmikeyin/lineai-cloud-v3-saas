<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DifyAppPool extends Model
{
    public const STATUS_AVAILABLE = 'available';
    protected $fillable = [
        'app_name',
        'app_api_key',
        'app_mode',
        'status',
        'assigned_tenant_id',
    ];

    public function assignedTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'assigned_tenant_id');
    }
}
