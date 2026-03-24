<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KnowledgeItem extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id','type','title','question','content','answer','status','sort_order',
        'is_ai_enabled','keywords','meta'
    ];

    protected $casts = [
        'is_ai_enabled' => 'boolean',
        'keywords' => 'array',
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
