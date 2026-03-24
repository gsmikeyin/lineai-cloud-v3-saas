<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    public const DIRECTION_INBOUND = 'inbound';
    public const DIRECTION_OUTBOUND = 'outbound';

    public const SENDER_CUSTOMER = 'customer';
    public const SENDER_AI = 'ai';
    public const SENDER_AGENT = 'agent';
    public const SENDER_SYSTEM = 'system';

    public const TYPE_TEXT = 'text';
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_STICKER = 'sticker';
    public const TYPE_FILE = 'file';
    public const TYPE_FLEX = 'flex';

    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'customer_id',
        'user_id',
        'direction',
        'sender_type',
        'message_type',
        'content',
        'line_message_id',
        'reply_token',
        'is_ai_generated',
        'is_read',
        'delivery_status',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'attachments',
        'raw_payload',
        'meta',
        'sent_at',
    ];

    protected $casts = [
        'is_ai_generated' => 'boolean',
        'is_read' => 'boolean',
        'attachments' => 'array',
        'raw_payload' => 'array',
        'meta' => 'array',
        'sent_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', self::DIRECTION_INBOUND);
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', self::DIRECTION_OUTBOUND);
    }

    public function scopeAi($query)
    {
        return $query->where('sender_type', self::SENDER_AI);
    }
}
