<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeItem extends Model
{
    use SoftDeletes;

    public const TYPE_FAQ = 'faq';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_POLICY = 'policy';
    public const TYPE_PROMPT = 'prompt';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'tenant_id',
        'type',
        'title',
        'question',
        'content',
        'answer',
        'status',
        'sort_order',
        'is_ai_enabled',
        'keywords',
        'meta',
    ];

    protected $casts = [
        'is_ai_enabled' => 'boolean',
        'keywords' => 'array',
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeAiEnabled($query)
    {
        return $query->where('is_ai_enabled', true);
    }
}