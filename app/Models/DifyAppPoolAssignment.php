<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DifyAppPoolAssignment extends Model
{
    protected $fillable = [
        'dify_app_pool_id',
        'tenant_id',
        'action',
        'remark',
    ];
}
