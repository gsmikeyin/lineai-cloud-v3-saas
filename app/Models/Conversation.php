<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
   public const STATUS_OPEN = 'open';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CLOSED = 'closed';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';

    protected $fillable = [
        'tenant_id',
        'external_conversation_id',
        'customer_id',
        'assigned_user_id',
        'channel',
        'status',
        'priority',
        'ai_enabled',
        'human_handoff',
        'last_message_at',
        'last_customer_message_at',
        'last_agent_reply_at',
        'closed_at',
        'meta',
        'unread_count',
        'last_read_at',
    ];

    protected $casts = [
        'ai_enabled' => 'boolean',
        'human_handoff' => 'boolean',
        'last_message_at' => 'datetime',
        'last_customer_message_at' => 'datetime',
        'last_agent_reply_at' => 'datetime',
        'closed_at' => 'datetime',
        'meta' => 'array',
        'last_read_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest('sent_at');
    }

    public function scopeTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function markAsRead(): void
    {
        $this->update([
           'unread_count' => 0,
           'last_read_at' => now(),
        ]);
    }

    public function incrementUnread(): void
    {
        $this->increment('unread_count');
    }
    
}
