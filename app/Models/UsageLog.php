<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageLog extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id','metric','value','period_date','period_type','meta'];

    protected $casts = [
        'value' => 'decimal:2',
        'period_date' => 'date',
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
