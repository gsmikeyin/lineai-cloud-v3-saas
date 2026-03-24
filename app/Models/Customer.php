<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id','source','line_user_id','display_name','avatar_url','language',
        'status','is_blocked','is_vip','phone','email','gender','birthday','city',
        'country','total_messages','total_orders','total_spent','first_interaction_at',
        'last_interaction_at','last_order_at','attributes','meta'
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
        'is_vip' => 'boolean',
        'birthday' => 'date',
        'total_spent' => 'decimal:2',
        'first_interaction_at' => 'datetime',
        'last_interaction_at' => 'datetime',
        'last_order_at' => 'datetime',
        'attributes' => 'array',
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function conversations(): HasMany { return $this->hasMany(Conversation::class); }
    public function messages(): HasMany { return $this->hasMany(Message::class); }
    public function tags(): BelongsToMany { return $this->belongsToMany(Tag::class, 'customer_tag')->withPivot('created_at'); }
}
