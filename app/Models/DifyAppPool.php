<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
