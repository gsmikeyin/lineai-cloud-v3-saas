<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id','created_by','name','trigger_type','status',
        'trigger_config','condition_config','action_config','run_count','last_run_at'
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'condition_config' => 'array',
        'action_config' => 'array',
        'last_run_at' => 'datetime',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
